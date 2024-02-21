# R2-WC-Product-Addon (R2-WCPA) 外掛介紹

>一句話說明：Woocommerce商品加價購

現在外掛有出的加購大概分為兩種<br>
一種是常說的加價購(像是WPC Frequently Bought Together)<br>
一種是叫bundle綑綁銷售(像是WPC Grouped Product)

差別比較像是

1. 加價購(該外掛是這一款)<br>
是A可以去自由的加B加C，每個商品都是各自存在的
EX：我買了一件衣服，可以再加多少錢買一件褲子

2. bundle綑綁銷售<br>
比較像是一個組合商品，把其他子商品都加進來綑綁銷售
EX：我有一套網路架站教學課程包，裏面包含了JS課程、HTML課程、CSS課程

## 使用方法

- 啟用外掛之後可以在商品資料中看到多一個頁籤叫加價購商品<br>
只有在簡單商品與可變商品會顯示<br>
功能：可以拖移順序、可以新增、可以刪除、可以設定價格
<img src="https://github.com/s0985514623/R2-WC-Product-Addon/assets/35906564/b2c54775-494c-4b44-90f2-abd798343fb9">

- 選擇要加入的商品<br>
商品分類會依分類層級顯示
<img src="https://github.com/s0985514623/R2-WC-Product-Addon/assets/35906564/dfd6e399-e5e8-4818-b29d-6ab01069ec10">

- 前台顯示<br>
Render 在這支hook上面，加入購物車按鈕之前<br>

```php
add_action('woocommerce_before_add_to_cart_button', 'r2_wcpa_render');
```
功能：可變商品的預設值會跟著加購商品預設值變動<br>
當改變屬性值的時候會自動勾選加購商品<br>
自訂AJAX事件，當加入購物車的時候會自動加入加購商品不刷新頁面<br>
目前設定是只有會員才能下單，非會員會跳出請登入視窗，未來有需要再單獨拉頁面出來設定<br>
當購物車中主商品不存在時，加購商品也會被移除<br>
有整合ELEMENTOR編輯器的側選單，如果有開啟Automatically Open Cart功能的話，加購商品也會自動打開
<img src="https://github.com/s0985514623/R2-WC-Product-Addon/assets/35906564/df9439ec-0d8f-4e8f-bbca-04b146bfe792">

- 購物車頁顯示<br>
Render 在這支hook上面，購物車表格之後<br>
```php
add_action('woocommerce_after_cart_table', 'r2_wcpa_render');
```
功能：已在購物車中的加購商品不會出現在下方列表<br>
如果有相同的加購商品則會合併取價格低的出現<br>
整合js事件added_to_cart刷新畫面<br>
點擊列表中的加購商品會跳到商品頁面並加上urlParams，再次加入購物車會是加價購的優惠價格
<img src="https://github.com/s0985514623/R2-WC-Product-Addon/assets/35906564/a73eb54a-c962-4be4-b0bd-631e6206d60d">


## 參考
- 1.React腳手架來源 [J7](https://github.com/j7-dev/wp-react-plugin)
- 2.外掛開發參考 [J7](https://github.com/j7-dev/wp-power-shop)