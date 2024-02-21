/* eslint-disable jsdoc/no-undefined-types */
/* eslint-disable no-undef */
import { r2_wcpa_handleChange, clearSelect, getAjaxNonce, defaultAddToCart, deleteCart, clickAddToCartBtn } from './custom/commonFunctions.js'
/**
 * 整合ajax流程
 * 拆解原本的ajax流程>取得nonce>刪除購物車>加入購物車>重新取得購物車
 * 1.getAjaxNonce 取得nonce後執行鍊式調用
  .then(
  function(data) { // 成功的回调
  console.log("Success: ", data);
  },
  function(jqXHR, textStatus, errorThrown) { // 失败的回调
  console.log("Error: ", textStatus, errorThrown);
  }
  );每個按鈕各自執行的handler獨立出來，判斷是否需要先移除購物車還是直接加入
 */

jQuery(document).ready(function ($) {
  //取得商品資訊
  const productInfo = window.r2_wcpa_data.products_info.products
  //改寫成通用版本，前端只需要判斷加入購物車，後端判斷是否有勾選只能加入一個商品
  $(document).on('click', 'button.single_add_to_cart_button:not(.bundle_add_to_cart_button)', function (event) {
    clickAddToCartBtn(event)
  })

  //v 綑綁商品
  $(document).on('click', '.single_add_to_cart_button[name].bundle_add_to_cart_button', function (event) {
    event.preventDefault()
    //創建一個空物件取得加購商品的數量
    const bundledProduct = {}
    $('.bundled_qty').each(function () {
      const bundledQtyName = $(this).attr('name')
      const bundledQty = $(this).val()
      //將結果添加到空物件當中
      bundledProduct[bundledQtyName] = bundledQty
    })

    //創建一個空物件取得已選擇的加購商品
    const bundledProductOptional = {}
    $('.bundled_product_checkbox:checked').each(function () {
      const bundledProductName = $(this).attr('name')
      bundledProductOptional[bundledProductName] = $(this).val()
    })
    const product_id = $(event.target).val()
    const quantity = 1
    const data = {
      ...bundledProduct,
      ...bundledProductOptional,
      product_id,
      quantity,
    }

    //取得原本文字
    const defaultText = event.target.innerHTML
    //loading狀態
    const loadingState = '<div class="h-[18px] flex justify-center items-center"><svg style="animation: spin 1s linear infinite" xmlns="http://www.w3.org/2000/svg" height="1rem" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg {fill: #ffffff}</style><path d="M222.7 32.1c5 16.9-4.6 34.8-21.5 39.8C121.8 95.6 64 169.1 64 256c0 106 86 192 192 192s192-86 192-192c0-86.9-57.8-160.4-137.1-184.1c-16.9-5-26.6-22.9-21.5-39.8s22.9-26.6 39.8-21.5C434.9 42.1 512 140 512 256c0 141.4-114.6 256-256 256S0 397.4 0 256C0 140 77.1 42.1 182.9 10.6c16.9-5 34.8 4.6 39.8 21.5z" /></svg></div>'
    event.target.innerHTML = loadingState

    //取得nonce後執行鍊式調用
    getAjaxNonce().then(
      function (nonce) {
        // 成功的後執行刪除購物車
        deleteCart({ data, nonce }).then(
          //刪除購物車成功
          function () {
            //加入購物車
            defaultAddToCart({ data }).then(
              //加入購物車成功
              function (res) {
                event.target.innerHTML = defaultText
                //成功會返回fragments / cart_hash參數
                $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash])
              },
              //加入購物車失敗
              function (jqXHR, textStatus, errorThrown) {
                // 失败的回调
                console.log('加入購物車失敗: ', textStatus, errorThrown)
              },
            )
          },
          //刪除購物車失敗
          function (jqXHR, textStatus, errorThrown) {
            // 失败的回调
            console.log('刪除購物車失敗: ', jqXHR, textStatus, errorThrown)
          },
        )
      },
      //取得nonce失敗
      function (jqXHR, textStatus, errorThrown) {
        // 失败的回调
        console.log('Error: ', textStatus, errorThrown)
      },
    )
  })

  //加購可變商品選項改變
  $(document).on('change', '.variableProduct select', function () {
    const select = $(this)
    const variableProductId = $(this).parents('.productAddon').data('product_addon_id')
    const variableProductInfo = productInfo.find((item) => item.id === variableProductId)
    $(this).closest('.productAddon').find('input[type=checkbox]').prop('checked', true)
    r2_wcpa_handleChange(select, variableProductInfo)
  })
  //清除連結
  $(document).on('click', '.clearLink a', clearSelect)
})
