/* eslint-disable jsdoc/no-undefined-types */
/* eslint-disable no-undef */
import { r2_wcpa_handleChange, clearSelect, addToCart } from './custom/commonFunctions.js'
/**加價購商品選擇與加入購物車方法 */

jQuery(document).ready(function ($) {
  const productInfo = window.r2_wcpa_cart_data.products_info
  const variableProduct = $('.variableProduct')
  const allClearLink = $('.clearLink a')
  //加購可變商品
  variableProduct.on('change', 'select', function () {
    const select = $(this)
    const variableProductId = $(this).parents('.productAddon').data('product_addon_id')
    const variableProductInfo = productInfo.map((item) => item.products.find((product) => product.id === variableProductId)).find((product) => product !== undefined)
    r2_wcpa_handleChange(select, variableProductInfo)
  })

  //清除連結
  allClearLink.on('click', clearSelect)

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
      const parent_product_id = $(item).data('parent_product_id')
      const product_addon_id = productAddon.data('product_addon_id')
      const variable_id = productAddon.data('variable_id')
      const data = {
        parent_product_id,
        product_id: product_addon_id,
        quantity: 1,
        variable_id,
        product_addon_price: parseInt(productAddon.find('.productAddonPrice .salesPrice').text().replace('NT$', '').replace(',', ''), 10),
      }
      addToCart({ event, data })
    }
    //如果該加購商品為簡單商品
    else if (productAddon.hasClass('simpleProduct')) {
      const parent_product_id = $(item).data('parent_product_id')
      const product_addon_id = productAddon.data('product_addon_id')
      const data = {
        parent_product_id,
        product_id: product_addon_id,
        quantity: 1,
        product_addon_price: parseInt(productAddon.find('.productAddonPrice .salesPrice').text().replace('NT$', '').replace(',', ''), 10),
      }
      addToCart({ event, data })
    }
  })
})
