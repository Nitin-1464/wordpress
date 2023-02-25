<?php
/**
 * Language tab - global - partial page.
 *
 * @package  Iubenda
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mb-3 p-0">

	<fieldset class="paste_embed_code tabs tabs--style2">

		<ul class="tabs__nav">
			<?php
			foreach ( iubenda()->languages as $k => $v ) :
				$_status = '';
				if ( iubenda()->lang_default === $k ) {
					$_status = 'active';
				}
				?>
				<li id="<?php echo esc_html( "code_{$k}-iubenda_{$service['name']}_solution_tab" ); ?>" class="tabs__nav__item <?php echo esc_html( $_status ); ?>" data-target="tab-<?php echo esc_html( $k ); ?>" data-group="language-tabs">
					<?php echo esc_html( strtoupper( $k ) ); ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php
		$product_helper = new Product_Helper();

		foreach ( $product_helper->get_languages() as $lang_id => $v ) :
			$method = "get_{$key}_embed_code_by_lang";
			$code   = $product_helper->{$method}( $lang_id );
			?>
			<div data-target="tab-<?php echo esc_html( $lang_id ); ?>" class="tabs__target <?php echo (string) iubenda()->lang_default === (string) $lang_id || 'default' === (string) $lang_id ? 'active' : ''; ?>" data-group="language-tabs">
				<textarea class='form-control text-sm m-0 iub-language-code iub-embed-code-<?php echo esc_html( $key ); ?>' data-language="<?php echo esc_html( $lang_id ); ?>" placeholder='<?php esc_html_e( 'Paste your embed code here', 'iubenda' ); ?>' name='iubenda_<?php echo esc_html( $service['name'] ); ?>_solution[code_<?php echo esc_html( $lang_id ); ?>]' rows='4'><?php echo esc_html( $code ); ?></textarea>
			</div>
		<?php endforeach; ?>

	</fieldset>
	<div class="text-right mt-2">
		<a target="_blank" href="<?php echo esc_url( iubenda()->settings->links[ "how_generate_{$key}" ] ); ?>" class="link link-helper"><span class="tooltip-icon mr-2">?</span><?php esc_html_e( 'Where can I find this code?', 'iubenda' ); ?></a>
	</div>
</div>
