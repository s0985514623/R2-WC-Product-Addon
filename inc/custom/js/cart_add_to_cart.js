/* eslint-disable jsdoc/no-undefined-types */
/* eslint-disable no-undef */
import { r2_wcpa_handleChange, clearSelect, addToCart } from './custom/commonFunctions.js'
/**加價購商品選擇與加入購物車方法 */

jQuery(document).ready(function ($) {
  const productInfo = window.r2_wcpa_cart_data
  //加購可變商品
  $(document).on('change', '.variableProduct select', function () {
    const select = $(this)
    const variableProductId = $(this).parents('.productAddon').data('product_addon_id')
    const variableProductInfo = Object.keys(productInfo).reduce((acc, key) => {
      if (key.includes(variableProductId)) {
        acc = productInfo[key]
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
      const data = {
        parent_product_id,
        product_id: product_addon_id,
        quantity: 1,
        variable_id,
      }
      addToCart({ event, data })
    }
    //如果該加購商品為簡單商品
    else if (productAddon.hasClass('simpleProduct')) {
      const parent_product_id = productAddon.data('parent_product_id')
      const product_addon_id = productAddon.data('product_addon_id')
      const data = {
        parent_product_id,
        product_id: product_addon_id,
        quantity: 1,
      }
      addToCart({ event, data })
    }
  })
})
