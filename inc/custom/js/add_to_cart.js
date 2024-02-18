/* eslint-disable jsdoc/no-undefined-types */
/* eslint-disable no-undef */
import { r2_wcpa_handleChange, clearSelect, addToCart } from './custom/commonFunctions.js'
/**加價購商品選擇與加入購物車方法 */

jQuery(document).ready(function ($) {
  const productInfo = window.r2_wcpa_data.products_info.products
  const variableProduct = $('.variableProduct')
  const allClearLink = $('.clearLink a')
  //加購可變商品
  variableProduct.on('change', 'select', function () {
    const select = $(this)
    const variableProductId = $(this).parents('.productAddon').data('product_addon_id')
    const variableProductInfo = productInfo.find((item) => item.id === variableProductId)
    // console.log('🚀 ~ variableProductInfo:', variableProductInfo)
    r2_wcpa_handleChange(select, variableProductInfo)
  })

  //清除連結
  allClearLink.on('click', clearSelect)

  //加入購物車
  $(document).on('click', '.single_add_to_cart_button[name]:not(.bundle_add_to_cart_button)', function (event) {
    event.preventDefault()
    //取得選擇的加價購商品
    const checkedProductAddon = $('.productAddon').find('input[type=checkbox]:checked')
    //取得選擇的加價購商品的父元素
    const productAddon = checkedProductAddon.parents('.productAddon')
    productAddon.each((_index, item) => {
      //如果該加購商品為可變商品
      if ($(item).hasClass('variableProduct')) {
        const parent_product_id = $(item).data('parent_product_id')
        const product_addon_id = $(item).data('product_addon_id')
        const variable_id = $(item).data('variable_id')
        const data = {
          parent_product_id,
          product_id: product_addon_id,
          quantity: 1,
          variable_id,
        }
        addToCart({ event, data })
      }
      //如果該加購商品為簡單商品
      else if ($(item).hasClass('simpleProduct')) {
        const parent_product_id = $(item).data('parent_product_id')
        const product_addon_id = $(item).data('product_addon_id')
        const data = {
          parent_product_id,
          product_id: product_addon_id,
          quantity: 1,
        }
        addToCart({ event, data })
      }
    })
  })
})
