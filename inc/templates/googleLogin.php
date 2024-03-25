<?php
// 解構賦值 $args.
[
	'current_page_url' => $current_page_url,
] = $args;

?>
<div
	class="noLoginPup flex flex-col fixed right-0 top-32 z-[1000] w-80 bg-white font-sans shadow-xl duration-300 animate__animated animate__fadeInRight bg-[url('/wp-content/uploads/2023/11/購物車登入-03.png')] bg-no-repeat bg-contain bg-[right_1rem_top_0.5rem]">
	<p class="flex items-center px-8 h-[100px] text-[#4562A8] font-semibold ">
		請先加入會員，<br>才能加入購物車哦！
	</p>
	<a class="flex items-center justify-center w-full bg-[#4562A8] text-sm font-semibold text-white h-10 gap-2 fill-white"
		href="<?php echo home_url(); ?>/wp-login.php?loginSocial=google&redirect=<?php echo $current_page_url; ?>" data-plugin="nsl" data-action="connect"
		data-redirect="<?php echo $current_page_url; ?>" data-provider="google" data-popupwidth="600" data-popupheight="600">
		<svg class="" class="google" xmlns="http://www.w3.org/2000/svg" height="1em"
			viewBox="0 0 488 512">
			<!--! Font Awesome Free 6.4.2 by @fontAwesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 FontIcons, Inc. -->
			<path
				d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z" />
		</svg>
		Google 登入
	</a>
	<a class="flex items-center justify-center w-full bg-[#06C755] text-sm font-semibold text-white h-10 gap-2 fill-white"
				href="<?php echo get_site_url(); ?>/wp-login.php?loginSocial=line&redirect=<?php echo $current_page_url; ?>"
				data-plugin="nsl" data-action="connect"
				data-redirect="<?php echo $current_page_url; ?>" data-provider="line"
				data-popupwidth="600" data-popupheight="600">
				<svg class="line" height="1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M311 196.8v81.3c0 2.1-1.6 3.7-3.7 3.7h-13c-1.3 0-2.4-.7-3-1.5l-37.3-50.3v48.2c0 2.1-1.6 3.7-3.7 3.7h-13c-2.1 0-3.7-1.6-3.7-3.7V196.9c0-2.1 1.6-3.7 3.7-3.7h12.9c1.1 0 2.4 .6 3 1.6l37.3 50.3V196.9c0-2.1 1.6-3.7 3.7-3.7h13c2.1-.1 3.8 1.6 3.8 3.5zm-93.7-3.7h-13c-2.1 0-3.7 1.6-3.7 3.7v81.3c0 2.1 1.6 3.7 3.7 3.7h13c2.1 0 3.7-1.6 3.7-3.7V196.8c0-1.9-1.6-3.7-3.7-3.7zm-31.4 68.1H150.3V196.8c0-2.1-1.6-3.7-3.7-3.7h-13c-2.1 0-3.7 1.6-3.7 3.7v81.3c0 1 .3 1.8 1 2.5c.7 .6 1.5 1 2.5 1h52.2c2.1 0 3.7-1.6 3.7-3.7v-13c0-1.9-1.6-3.7-3.5-3.7zm193.7-68.1H327.3c-1.9 0-3.7 1.6-3.7 3.7v81.3c0 1.9 1.6 3.7 3.7 3.7h52.2c2.1 0 3.7-1.6 3.7-3.7V265c0-2.1-1.6-3.7-3.7-3.7H344V247.7h35.5c2.1 0 3.7-1.6 3.7-3.7V230.9c0-2.1-1.6-3.7-3.7-3.7H344V213.5h35.5c2.1 0 3.7-1.6 3.7-3.7v-13c-.1-1.9-1.7-3.7-3.7-3.7zM512 93.4V419.4c-.1 51.2-42.1 92.7-93.4 92.6H92.6C41.4 511.9-.1 469.8 0 418.6V92.6C.1 41.4 42.2-.1 93.4 0H419.4c51.2 .1 92.7 42.1 92.6 93.4zM441.6 233.5c0-83.4-83.7-151.3-186.4-151.3s-186.4 67.9-186.4 151.3c0 74.7 66.3 137.4 155.9 149.3c21.8 4.7 19.3 12.7 14.4 42.1c-.8 4.7-3.8 18.4 16.1 10.1s107.3-63.2 146.5-108.2c27-29.7 39.9-59.8 39.9-93.1z"/></svg>
				Line 登入
			</a>
	<div onclick="closePup()" class="closeBtn absolute top-4 right-4 cursor-pointer"><svg
			class="fill-[#374a6d]" xmlns="http://www.w3.org/2000/svg" height="1.25em"
			viewBox="0 0 384 512">
			<!--! Font Awesome Free 6.4.2 by @fontAwesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 FontIcons, Inc. -->
			<path
				d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z" />
		</svg></div>
</div>
<script>
const closePup = () => {
	const LoginPup = jQuery('.noLoginPup');
	LoginPup.removeClass('animate__fadeInRight');
	LoginPup.addClass('animate__fadeOutRight');
}
</script>
