import { useRef, useCallback } from 'react'
import { Form, Spin } from 'antd'
import Add from './Add'
import AddedItem from './AddedItem'
import { addedProductsAtom } from './atoms'
import { useAtom } from 'jotai'
import { snake, power_shop_meta_meta_id as metaId } from '@/utils'

import { TProduct } from '@/types/wcRestApi'
import { LoadingWrap, LoadingCard } from '@/components/PureComponents'
import SaveButton from './SaveButton'
import { DndProvider } from 'react-dnd'
import { HTML5Backend } from 'react-dnd-html5-backend'
import update from 'immutability-helper'
import { SaveFilled } from '@ant-design/icons'
import usePSMeta from './hooks/usePSMeta'
import useChangeNotification from './hooks/useChangeNotification'
import useAddProductSave from './hooks/useAddProductSave'
import useHandleShopMeta from './hooks/useHandleShopMeta'
import useGetAddProducts from './hooks/useGetAddProducts'

export const tinyMCESaveBtn = document.getElementById('publish') as HTMLInputElement | null // 因為發布與更新的 按鈕不同
export const blockEditorSaveBtn = document.querySelector('[class*="editor-post-publish-button"]') as HTMLInputElement | null
export const fieldNode = document.getElementById(`meta-${metaId}-value`) as HTMLInputElement | null

const AddProduct = () => {
  const { shop_meta, isLoading: isPSMetaLoading } = usePSMeta()

  const shop_meta_product_ids = shop_meta.map((item) => item.productId)

  const productsResult = useGetAddProducts(shop_meta_product_ids)
  // console.log('🚀 ~ productsResult:', productsResult)
  const [addedProducts, setAddedProducts] = useAtom(addedProductsAtom)

  const [form] = Form.useForm()
  const ref = useRef<HTMLInputElement>(null)

  const { handleSave: _ } = useAddProductSave({
    form,
    isPSMetaLoading,
    productsResult,
    shop_meta,
  })

  const { isLoading: isHandleShopMetaLoading } = useHandleShopMeta({
    productsResult,
    shop_meta,
  })

  const { handleFormChange } = useChangeNotification(form)

  const moveCard = useCallback((dragIndex: number, hoverIndex: number) => {
    setAddedProducts((pre: TProduct[]) =>
      update(pre, {
        $splice: [
          [
            dragIndex,
            1,
          ],
          [
            hoverIndex,
            0,
            pre[dragIndex] as TProduct,
          ],
        ],
      }),
    )
    handleFormChange()
  }, [])

  const renderItem = useCallback((product: TProduct, index: number) => {
    return <AddedItem key={product.id} index={index} product={product} moveCard={moveCard} />
  }, [])
  return (
    <div className="p-4">
      {(isPSMetaLoading || isHandleShopMetaLoading) && <LoadingWrap />}
      <Form className="pt-4" layout="vertical" form={form} onValuesChange={handleFormChange}>
        <div className="flex justify-between mb-4">
          <SaveButton type="primary" icon={<SaveFilled />} disabled={isPSMetaLoading || productsResult?.isFetching || isHandleShopMetaLoading} />
        </div>
        <DndProvider backend={HTML5Backend}>
          {productsResult?.isLoading || productsResult?.isFetching
            ? [
                1,
                2,
                3,
              ].map((i) => <LoadingCard ratio="h-[8rem]" key={i} />)
            : addedProducts.map((product, i) => renderItem(product, i))}
        </DndProvider>

        <Add />
      </Form>
    </div>
  )
}

export default AddProduct
