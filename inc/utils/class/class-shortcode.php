<?php

declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

use J7\WpMyAppPlugin\MyApp\Inc\Bootstrap;

class ShortCode
{

    function __construct($shortcode = '')
    {
        if (!empty($shortcode)) {
            \add_shortcode($shortcode, [ $this, 'shortcode_callback' ]);
        }
    }

    public function shortcode_callback()
    {

        $html = '';
        ob_start();
        ?>
		<div id="<?=Bootstrap::RENDER_ID_1?>"></div>
<?php
$html .= ob_get_clean();

        return $html;
    }
}
