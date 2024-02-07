import { FormInstance, notification } from 'antd'
import { useSetAtom } from 'jotai'
import { isChangeAtom } from '../atoms'
import { useUpdate, useAjax } from '@/hooks'
import { kebab, postId, snake, formatShopMeta } from '@/utils'

const useSave = (form: FormInstance) => {
  const setIsChange = useSetAtom(isChangeAtom)
  //改用ajax 打是因為wc跟wp的api 會查不到post id
  const { mutate, isLoading } = useAjax({
    mutationOptions: {
      onSuccess: () => {
        notification.success({
          message: '加價購商品 儲存成功',
        })
        setIsChange(false)
        notification.destroy('saveNotification')
      },
      onError: (error) => {
        console.log('Error', error)
        notification.error({
          message: '儲存失敗',
        })
      },
    },
  })

  // const { mutate, isLoading } = useUpdate({
  //   resource: 'products',
  //   dataProvider: 'wc',
  //   pathParams: [postId],
  //   mutationOptions: {
  //     onSuccess: () => {
  //       notification.success({
  //         message: 'Power Shop 儲存成功',
  //       })
  //       setIsChange(false)
  //       notification.destroy('saveNotification')
  //     },
  //     onError: (error) => {
  //       console.log('Error', error)
  //       notification.error({
  //         message: '儲存失敗',
  //       })
  //     },
  //   },
  // })

  const handleSave = async () => {
    const allFields = await formatShopMeta({ form })
    mutate({
      action: 'addon_handle_update_post_meta',
      post_id: postId as number,
      meta_key: `${snake}_meta`,
      meta_value: JSON.stringify(allFields),
    })

    // mutate({
    //   meta: {
    //     [`${snake}_meta`]: JSON.stringify(allFields),
    //   },
    // })
  }

  return {
    handleSave,
    isLoading,
  }
}

export default useSave
