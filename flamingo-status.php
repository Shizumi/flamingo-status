<?php
/**
 * Plugin Name: Flamingo Status
 * Version: 0.0.1
 * Author: Shizumi
 * Author URI: https://blog.spicadots.com/
 * Created : January 22, 2020
 * Modified: January 22, 2020
 * Text Domain: flamingo-status
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

class Flamingo_Status {
	public $status;
	public $statuses;

	public function __construct() {
		$this->statuses = [
			'yet'   => esc_html( __( '未対応', 'flamingo-status' ) ),
			'draft' => esc_html( __( '下書き済み', 'flamingo-status' ) ),
			'done'  => esc_html( __( '返信済み', 'flamingo-status' ) ),
		];

		add_action( 'admin_menu', [ $this, 'fs_add_menu' ] );
	}

	/**
	 * メタボックスの追加フック設定
	 */
	public function fs_add_menu() {
		add_action( 'load-flamingo_page_flamingo_inbound', [ $this, 'fs_add_meta_box' ], 11 );
		add_action( 'check_admin_referer', [ $this, 'fs_set_status' ], 10, 2 );
		add_filter( 'manage_flamingo_inbound_posts_columns', [ $this, 'fs_add_columns' ], 11 );
		add_filter( 'manage_flamingo_inbound_posts_custom_column', [ $this, 'fs_add_column_data' ], 11, 2 );
	}

	/**
	 * メタボックス追加処理
	 */
	public function fs_add_meta_box() {
	 	add_meta_box( 'fs_meta_box', __( '対応ステータス', 'flamingo-status' ), [ $this, 'fs_meta_box_in' ], null, 'side', 'core' );
	}

	/**
	 * メタボックス出力内容設定
	 */
	public function fs_meta_box_in( $post ) {
		$post_id = $post->id;

		$meta_status = get_post_meta( $post_id, '_fs_status', true );

		$this->status = array_key_exists( $meta_status, $this->statuses ) ? $meta_status : 'yet';
?>
<div>
	<fieldset>
		<?php foreach ( $this->statuses as $status_id => $status ) : ?>
			<label><input type="radio"<?php checked( $this->status, $status_id ); ?> name="return_fs_status" value="<?php echo $status_id; ?>"><?php echo $status; ?></label><br />
		<?php endforeach; ?>
	</fieldset>
</div>
<?php
	}

	public function fs_set_status( $action ) {
		if ( isset( $_POST['post'] ) && 'flamingo-update-inbound_' . $_POST['post'] === $action ) {
			$this->status = $_POST['return_fs_status'];

			update_post_meta( $_POST['post'], '_fs_status', $this->status );

			return;
		}
		return;
	}

	public function fs_add_columns( $columns ) {
		$columns['status'] = __( '返信ステータス', 'flamingo-status' );
		return $columns;
	}

	public function fs_add_column_data( $column_name, $id ) {
		if ( 'status' == $column_name ) {
			$status = get_post_meta( $id, '_fs_status', true ) ? : 'yet';
			if ( array_key_exists( $status, $this->statuses ) ) {
				echo $this->statuses[ $status ];
			}
		}
		return;
	}
}

new Flamingo_Status();
