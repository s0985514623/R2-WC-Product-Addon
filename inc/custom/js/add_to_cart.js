/* eslint-disable jsdoc/no-undefined-types */
/* eslint-disable no-undef */
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
    handleChange(select, variableProductInfo)
  })

  // simpleProduct.on('click', 'input[type=checkbox]', handleClick)

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
        const product_addon_id = $(item).data('product_addon_id')
        const variable_id = $(item).data('variable_id')
        const data = {
          product_id: product_addon_id,
          quantity: 1,
          variable_id,
          product_addon_price: parseInt($(item).find('.productAddonPrice .salesPrice').text().replace('NT$', ''), 10),
        }
        addToCart({ event, data })
      }
      //如果該加購商品為簡單商品
      else if ($(item).hasClass('simpleProduct')) {
        const product_addon_id = $(item).data('product_addon_id')
        const data = {
          product_id: product_addon_id,
          quantity: 1,
          product_addon_price: parseInt($(item).find('.productAddonPrice .salesPrice').text().replace('NT$', ''), 10),
        }
        addToCart({ event, data })
      }
    })
  })
})

/**
 * 當select改變時執行各項操作
 *
 * @param {jQuery} select              畫面上選擇的select對象
 * @param {Object} variableProductInfo 當前可變商品變體對象
 *
 */
const handleChange = (select, variableProductInfo) => {
  //紀錄原始價格
  const productAddonPrice = select.parents('.productAddon').find('.productAddonPrice')
  const regularPrice = productAddonPrice.find('.regularPrice')
  const salesPrice = productAddonPrice.find('.salesPrice')

  //取得清除按鈕
  const clearLink = select.parents('.productAddon').find('.clearLink[data-product_addon_id="' + variableProductInfo.id + '"]')

  //儲存選擇的屬性
  const allSelect = select.parents('table').find('select')
  const selectedObj = {}
  jQuery.each(allSelect, (_index, item) => {
    const jQueryItem = jQuery(item) // 將 item 轉換為 jQuery 對象
    selectedObj[jQueryItem.data('label_key')] = jQueryItem.val()
  })

  //如果所有的select都有值
  if (hasNoEmptyValues(selectedObj)) {
    //循環變體判斷是否屬性相同
    const isCheckVariable = variableProductInfo.variations.filter((element) => {
      //如果選擇的屬性和變體的屬性相同,執行updatePrice並返回true跳出迴圈
      if (isEquivalent(selectedObj, element.attributes)) {
        //將選擇的變體id存入productAddon
        select.parents('.productAddon').data('variable_id', element.variation_id)
        updatePrice(select, element.regularPrice, element.salesPrice)
        clearLink.find('span').remove()
        return true
      }
      return false
    })

    //如果選擇的屬性和變體的屬性不相同,則改變金額為原價,並則顯示清除連結
    if (isCheckVariable.length === 0) {
      regularPrice.find('del').text(`NT$ ${regularPrice.data('original_price')}`)
      salesPrice.text(`NT$ ${salesPrice.data('original_price')}`)
      //如果沒有符合的選項,則顯示提示(如果已經有提示則不顯示)
      if (clearLink.find('span').length === 0) clearLink.prepend('<span>沒有符合的選項,請重新選擇</span>')
    }
  }
  //判斷selectedObj是否為空對象
  else if (hasEmptyValues(selectedObj)) {
    //如果為空對象,則不顯示清除連結
    clearLink.hide()
  }
  //如果部分有值
  else {
    //禁用其他屬性(有bug先不使用,向上選擇時不會執行,向下選擇時沒問題)
    // disabledOtherAttribute(select, selectedObj, variableProductInfo)

    //返回原價並顯示清除連結
    regularPrice.find('del').text(`NT$ ${regularPrice.data('original_price')}`)
    salesPrice.text(`NT$ ${salesPrice.data('original_price')}`)
    clearLink.show()
  }
}
/**檢查屬性是否都已選擇
 * @param {object} selectedObj 當前已選擇的selected 屬性對象
 * @returns {boolean} 如果對象中有空值，則返回false，否則返回true
 */
const hasNoEmptyValues = (selectedObj) => {
  return Object.values(selectedObj).every((value) => {
    // 根据需要检查空值的条件
    return value !== null && value !== undefined && value !== ''
  })
}
/**檢查屬性是否有空值
 * @param {object} selectedObj 當前已選擇的selected 屬性對象
 * @returns {boolean} 如果對象中有空值，則返回false，否則返回true
 */
const hasEmptyValues = (selectedObj) => {
  return Object.values(selectedObj).every((value) => {
    // 根据需要检查空值的条件
    return value === null || value === undefined || value === ''
  })
}

/**判斷兩組陣列或對象是否相等
 * @param {object} selectedObj 當前已選擇的selected 屬性對象
 * @param {object} obj2 用來比對的變體對象
 * @returns {boolean} 如果兩個對象相等，則返回true，否則返回false
 */
const isEquivalent = (selectedObj, obj2) => {
  const obj1Keys = Object.keys(selectedObj)
  const obj2Keys = Object.keys(obj2)
  // 检查键的数量是否相同
  if (obj1Keys.length !== obj2Keys.length) {
    return false
  }
  // 检查键和值是否相同
  for (const key of obj1Keys) {
    if (selectedObj[key] !== obj2[key]) {
      return false
    }
  }
  // 如果所有键和值都相同，则对象相等
  return true
}

/**改變金額及變更attr variable_id
 * @param {jQuery}select 畫面上選擇的select對象
 * @param {string}regularPrice 原價
 * @param {string}salesPrice 特價
 */
const updatePrice = (select, regularPrice, salesPrice) => {
  const productAddonPrice = select.parents('.productAddon').find('.productAddonPrice')
  const oldRegularPrice = productAddonPrice.find('.regularPrice del')
  const oldSalesPrice = productAddonPrice.find('.salesPrice')
  oldRegularPrice.text(`NT$ ${regularPrice}`)
  oldSalesPrice.text(`NT$ ${salesPrice}`)
}

/***
 * 有bug先不使用
 * Bug:向下選擇時沒問題,但是向上選擇時不會執行
 * 禁用其他屬性
 * 1.找出畫面上選擇的元素,並只取得有值的屬性
 * 2.跟變體的屬性比較,找出符合的變體
 * 3.循環該可變商品的其他select對象,根據符合的變體啟用屬性
 * @param {jQuery}select 						 畫面上選擇的select對象
 * @param {object}selectedObj      當前已選擇的selected 屬性
 * @param {object}variableProductInfo 變體對象
 *
 */
const disabledOtherAttribute = (select, selectedObj, variableProductInfo) => {
  //1.找出畫面上選擇的元素,並只取得有值的屬性
  const objKeys = Object.keys(selectedObj)
  const formatSelectedObj = objKeys.reduce((acc, cur) => {
    if (selectedObj[cur] !== undefined && selectedObj[cur] !== '' && selectedObj[cur] !== null) acc[cur] = selectedObj[cur]
    return acc
  }, {})

  //2.跟變體的屬性比較,找出符合的變體
  const variationsArr = variableProductInfo.variations
  const enabledVariationsArr = findAndUpdateVariants(formatSelectedObj, variationsArr)
  // console.log('🚀 ~ enabledVariationsArr:', enabledVariationsArr)

  //3.循環該可變商品的其他select對象,根據符合的變體啟用屬性
  const otherSelects = select.parents('table').find('select').not(select)
  otherSelects.each((_i1, selectItem) => {
    const jQuerySelect = jQuery(selectItem)
    const labelKey = jQuerySelect.data('label_key')
    const options = jQuerySelect.find('option')
    options.each((_i2, option) => {
      const jQueryOption = jQuery(option)
      const optionValue = jQueryOption.val()
      const isDisabled = !enabledVariationsArr.some((variant) => {
        return variant.attributes[labelKey] === optionValue
      })
      if (isDisabled && optionValue !== '') {
        jQueryOption.attr('disabled', 'disabled')
      } else {
        jQueryOption.removeAttr('disabled')
      }
    })
  })
}

/**
 * 跟變體的屬性比較,找出符合的變體
 *
 * @param {Object} formatSelectedObj 畫面上選擇的屬性
 * @param {Object} variationsArr     變體陣列
 */
const findAndUpdateVariants = (formatSelectedObj, variationsArr) => {
  return variationsArr.filter((variant) => {
    for (const key in formatSelectedObj) {
      if (!variant.attributes.hasOwnProperty(key) || variant.attributes[key] !== formatSelectedObj[key]) {
        return false //如果發現不匹配的屬性,則直接返回false 結束迴圈
      }
    }
    return true //如果所有屬性都匹配,則返回true
  })
}

/**
 * 調用方法:清除當前productAddon 中所有select 的值
 */
function clearSelect() {
  const clearLink = jQuery(this).parents('.clearLink')
  const product_addon_id = clearLink.data('product_addon_id')
  const productAddon = jQuery('.productAddon[data-product_addon_id="' + product_addon_id + '"]')
  //清空所有select的值
  const allSelect = productAddon.find('select')
  const options = allSelect.find('option')
  options.each((_i2, option) => {
    const jQueryOption = jQuery(option)
    jQueryOption.removeAttr('disabled')
  })
  allSelect.val('')
  //返回原價並隱藏清除連結
  const productAddonPrice = productAddon.find('.productAddonPrice')
  const regularPrice = productAddonPrice.find('.regularPrice')
  const salesPrice = productAddonPrice.find('.salesPrice')
  regularPrice.find('del').text(`NT$ ${regularPrice.data('original_price')}`)
  salesPrice.text(`NT$ ${salesPrice.data('original_price')}`)
  clearLink.hide()
}

//加入購物車代碼
async function addToCart({ _event, data }) {
  // console.log('🚀 ~ data:', data)
  // //取得原本文字
  // const defaultText = event.target.innerHTML.hasClass('isLoading')
  // //loading狀態
  // const loadingState =
  //   '<div class="isLoading h-[18px] flex justify-center items-center"><svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" height="1rem" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg {fill: #ffffff}</style><path d="M222.7 32.1c5 16.9-4.6 34.8-21.5 39.8C121.8 95.6 64 169.1 64 256c0 106 86 192 192 192s192-86 192-192c0-86.9-57.8-160.4-137.1-184.1c-16.9-5-26.6-22.9-21.5-39.8s22.9-26.6 39.8-21.5C434.9 42.1 512 140 512 256c0 141.4-114.6 256-256 256S0 397.4 0 256C0 140 77.1 42.1 182.9 10.6c16.9-5 34.8 4.6 39.8 21.5z" /></svg></div>'
  $.ajax({
    type: 'GET',
    url: `${wpApiSettings.root}/wrp/ajaxnonce`,
    success(nonceRes) {
      const nonce = nonceRes.nonce
      $.ajax({
        type: 'POST',
        url: r2_wcpa_data.env.ajaxUrl,
        data: {
          action: 'handle_add_to_cart',
          nonce,
          product_id: data.product_id,
          quantity: data.quantity,
          variable_id: data.variable_id ?? 0,
          product_addon_price: data.product_addon_price,
        },
        success(res) {
          //成功會返回fragments / cart_hash參數
          $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash])
          //錯誤會返回error:true / product_url參數
        },
        error(error) {
          //接上r2-member-filter外掛的class-user-is-login:未登入時返回登入視窗
          //如果已經有登入視窗就不再重複添加
          if ($('body').find('.noLoginPup').length > 0) {
            const LoginPup = $('.noLoginPup')
            LoginPup.addClass('animate__fadeInRight')
            LoginPup.removeClass('animate__fadeOutRight')
          } else {
            // 從 response 中獲取 HTML 內容
            const responseText = error.responseText
            // 使用 jQuery 創建一個虛擬元素來解析 HTML
            const virtualElement = $('<div>').html(responseText)
            // 提取 <div> 元素
            const divElement = virtualElement.find('.noLoginPup')
            // 提取 <script> 元素=>第一段是tailwindCss CDN 第二段是JS
            const scriptElement = virtualElement.find('script')
            // 將 <div> 元素添加到 body 中
            $('body').append(divElement)
            // $("body").append(`${scriptElement[0].outerHTML}`);
            $('body').append(`<script>${scriptElement[1].innerHTML}</script>`)
          }
        },
      })
    },
  })
}
