/* eslint-disable jsdoc/no-undefined-types */
/* eslint-disable no-undef */
import { r2_wcpa_handleChange, clearSelect, getAjaxNonce, addToCart } from './custom/commonFunctions.js'
/**加價購商品選擇與加入購物車方法 */

jQuery(document).ready(function ($) {
  //加購可變商品選項改變
  $(document).on('change', '.variableProduct select', function () {
    const select = $(this)
    const variableProductId = $(this).parents('.productAddon').data('product_addon_id')
    const variableProductInfo = Object.keys(window.r2_wcpa_cart_data).reduce((acc, key) => {
      if (key.includes(variableProductId)) {
        acc = window.r2_wcpa_cart_data[key]
      }
      return acc
    }, {})
    r2_wcpa_handleChange(select, variableProductInfo.product)
  })
  //清除連結
  $(document).on('click', '.clearLink a', clearSelect)
  //加入購物車
  $(document).on('click', '.productAddToCart a', function (event) {
    event.preventDefault()
    //取得選擇的加價購商品
    const thisButton = $(event.target)
    const loading = event.target.firstChild
    loading.classList.remove('hidden')
    //取得選擇的加價購商品的父元素
    const productAddon = thisButton.parents('.productAddon')
    //如果該加購商品為可變商品
    if (productAddon.hasClass('variableProduct')) {
      const parent_product_id = productAddon.data('parent_product_id')
      const product_addon_id = productAddon.data('product_addon_id')
      const variable_id = productAddon.data('variable_id')
      const data = [
        {
          parent_product_id,
          product_id: product_addon_id,
          quantity: 1,
          variable_id,
        },
      ]
      getAjaxNonce().then(
        function (nonce) {
          addToCart(data, nonce).then(function (_res) {
            $(document.body).trigger('wc_update_cart')
            // $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash])
          })
        },
        function (error) {
          console.log('error', error)
        },
      )
    }
    //如果該加購商品為簡單商品
    else if (productAddon.hasClass('simpleProduct')) {
      const parent_product_id = productAddon.data('parent_product_id')
      const product_addon_id = productAddon.data('product_addon_id')
      const data = [
        {
          parent_product_id,
          product_id: product_addon_id,
          quantity: 1,
        },
      ]
      getAjaxNonce().then(
        function (nonce) {
          addToCart(data, nonce).then(function (_res) {
            $(document.body).trigger('wc_update_cart')
            // $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash])
          })
        },
        function (error) {
          console.log('error', error)
        },
      )
    }
  })
  //當購物車更新時，變更window.r2_wcpa_cart_data購物車資料
  $(document.body).on('updated_wc_div', function () {
    $.ajax({
      type: 'POST',
      url: r2_wcpa_data.env.ajaxUrl,
      data: {
        action: 'handle_update_cart_data',
      },
      success(cart_data_res) {
        window.r2_wcpa_cart_data = cart_data_res
      },
      error(error) {
        console.log('added_to_cart error', error)
      },
    })
  })
})
