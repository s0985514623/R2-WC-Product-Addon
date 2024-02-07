import { TPSMeta, TPSVariation } from '@/types'
import { TProduct } from '@/types/wcRestApi'
import { useQueryClient } from '@tanstack/react-query'
import { notification } from 'antd'
import { useUpdate } from '@/hooks'
import { kebab, postId, snake } from '@/utils'
import { useEffect } from 'react'
import { TResult } from './useGetAddProducts'

type TUseHandleShopMetaProps = {
  productsResult: TResult<TProduct>
  shop_meta: TPSMeta[]
}

/**
 * 如果 shop_meta 紀錄的商品型態有改變，則更新 shop_meta
 */

const useHandleShopMeta = ({ productsResult, shop_meta }: TUseHandleShopMetaProps) => {
  // console.log('🚀 ~ shop_meta:', shop_meta)
  const queryClient = useQueryClient()
  const { mutate, ...restUpdateResult } = useUpdate({
    resource: kebab,
    dataProvider: 'wp',
    pathParams: [postId],
    mutationOptions: {
      onSuccess: () => {
        notification.success({
          key: 'handleShopMeta',
          message: '偵測到商品型態有變更',
          description: '資料已更新成功，帶入初始價格，請再次檢視商品價格',
        })
        notification.destroy('saveNotification')
        queryClient.invalidateQueries({
          queryKey: [
            'get_post_meta',
            postId,
            `${snake}_meta`,
          ],
        })
      },
      onError: (error) => {
        console.log('Error', error)
        notification.error({
          key: 'handleShopMeta',
          message: '偵測到商品型態有變更',
          description: '資料更新失敗，請重新整理，再試一次',
        })
      },
    },
  })

  const products = (productsResult?.data?.data || []) as TProduct[]

  const needUpdate = shop_meta.some((item) => {
    const metaProductId = item?.productId
    const metaProductType = getMetaProductType(item)
    const findProduct = products?.find((p) => p.id === metaProductId)
    return findProduct?.type ? findProduct?.type !== metaProductType : false
  })

  // 更新 shop_meta

  useEffect(() => {
    if (needUpdate) {
      const handled_shop_meta = shop_meta.map((item) => {
        const metaProductId = item?.productId
        const findProduct = products?.find((p) => p.id === metaProductId)

        if (findProduct?.type === 'simple') {
          return {
            productId: findProduct?.id,
            productType: findProduct?.type,
            regularPrice: Number(findProduct?.regular_price),
            salesPrice: Number(findProduct?.sale_price),
          }
        }

        if (findProduct?.type === 'variable') {
          const variations: TPSVariation[] | undefined = findProduct?.productVariations?.map((v) => {
            return {
              regularPrice: Number(v?.regular_price),
              salesPrice: Number(v?.sale_price),
              variationId: v?.id,
            }
          })
          return {
            productId: findProduct?.id,
            productType: findProduct?.type,
            variations,
          }
        }

        return item
      })

      mutate({
        meta: {
          [`${snake}_meta`]: JSON.stringify(handled_shop_meta),
        },
      })
    }
  }, [needUpdate])

  return restUpdateResult
}

function getMetaProductType(item: TPSMeta) {
  // console.log('🚀 ~ item:', item)
  //  因為舊版的可能沒有 productType 屬性，就要用 有沒有 variations 這個 key 來判斷

  if (item?.productType) {
    return item?.productType
  }

  if (item?.variations) {
    return 'variable'
  }

  return 'simple'
}

export default useHandleShopMeta
