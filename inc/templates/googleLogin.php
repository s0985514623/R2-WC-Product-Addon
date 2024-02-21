<div
	class="noLoginPup flex flex-col fixed right-0 top-32 z-[1000] w-80 bg-white font-sans shadow-xl duration-300 animate__animated animate__fadeInRight bg-[url('/wp-content/uploads/2023/11/購物車登入-03.png')] bg-no-repeat bg-contain bg-[right_1rem_top_0.5rem]">
	<p class="flex items-center px-8 h-[100px] text-[#4562A8] font-semibold ">
		請先加入會員，<br>才能加入購物車哦！
	</p>
	<a class="flex items-center justify-center w-full bg-[#4562A8] text-sm font-semibold text-white h-10 gap-2 fill-white"
		href="<?=home_url()?>/wp-login.php?loginSocial=google" data-plugin="nsl" data-action="connect"
		data-redirect="current" data-provider="google" data-popupwidth="600" data-popupheight="600">
		<svg class="" class="google" xmlns="http://www.w3.org/2000/svg" height="1em"
			viewBox="0 0 488 512">
			<!--! Font Awesome Free 6.4.2 by @fontAwesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 FontIcons, Inc. -->
			<path
				d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z" />
		</svg>
		Google 登入
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