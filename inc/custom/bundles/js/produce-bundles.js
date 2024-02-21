/* eslint-disable jsdoc/no-undefined-types */
/* eslint-disable no-undef */
const clickHandler = (event) => {
  const thisButton = event.target
  const loading = event.target.firstChild
  loading.classList.remove('hidden')
  const href = thisButton.getAttribute('data-href')
  const parentsId = thisButton.getAttribute('data-parentsId')
  const parameters = getURLParameters(href)
  const args = {
    parentsId, //綑綁商品id，加入與刪除商品都是同一個id
    quantity: 1, //加入購物車數量
    ...parameters,
  }
  sendAjax(args)
}
const getURLParameters = (url) => {
  const searchParams = new URL(url).searchParams
  const parameters = {}
  searchParams.forEach((value, key) => {
    parameters[key] = value
  })
  return parameters
}
const sendAjax = (args) => {
  //先做刪除購物車
  jQuery.ajax({
    url: produce_bundles_ajax_obj.ajaxUrl,
    data: {
      action: 'produce_bundles',
      parentsId: args.parentsId,
      nonce: produce_bundles_ajax_obj.nonce,
    },
    success(data) {
      //成功後執行加入購物車
      jQuery.ajax({
        type: 'POST',
        url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
        data: args,
        dataType: 'json',
        complete(res) {
          jQuery(document.body).trigger('wc_update_cart')
        },
        success(res) {
          jQuery(document.body).trigger('wc_update_cart')
        },
        error(errorThrown) {
          //TODO 因為嚴格模式下dataType: "json"這裡雖然打成功但會跳error=>之後進行檢查
          if (errorThrown.status === 200) {
            //如果status為成功就刷新頁面
            jQuery(document.body).trigger('wc_update_cart')
          }
        },
      })
    },
    error(errorThrown) {
      console.log('刪除購物車error', errorThrown)
    },
  })
}
