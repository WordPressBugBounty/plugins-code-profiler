<?php
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://nintechnet.com/codeprofiler/         |
 +=====================================================================+
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// =====================================================================
// Display the profiler's log.

// Delete the log?
if (! empty( $_POST['cp-delete-log'] ) ) {
	if ( empty( $_POST['cp_nonce'] ) || ! wp_verify_nonce( $_POST['cp_nonce'], 'cp_delete_log') ) {
		printf(
			CODE_PROFILER_ERROR_NOTICE,
			esc_html__('Security nonce is missing, try to reload the page.', 'code-profiler')
		);

	} else {
		$res = file_put_contents( CODE_PROFILER_LOG, "<?php exit; ?>\n");
		if ( $res !== false ) {
			printf(
				CODE_PROFILER_UPDATE_NOTICE,
				esc_html__('The log was deleted.', 'code-profiler')
			);
		} else {
			printf(
				CODE_PROFILER_ERROR_NOTICE,
				esc_html__('Cannot delete the log, make sure it is writable.', 'code-profiler')
			);
		}
	}
}
?>
<script>
'use strict';
var cplog_array = new Array();
<?php
$info = 0; $warn = 0; $error = 0; $debug = 0; $logline = '';
if ( file_exists( CODE_PROFILER_LOG ) ) {

	$lines = array();
	$lines = file( CODE_PROFILER_LOG, FILE_SKIP_EMPTY_LINES );
	$i = 0;
	$facility = array( 1 => 'INFO ', 2 => 'WARN ', 4 => 'ERROR', 8 => 'DEBUG');
	foreach( $lines as $line ) {
		if ( $line[0] == '<') { continue; }
		list( $date, $level, $string ) = explode('~~', $line, 3 );
		if (! isset( $facility[$level] ) ) {
			continue;
		}
		$date = date('d-M-y H:i:s', $date );
		echo 'cplog_array[' . $i . '] = "' .
				rawurlencode( "$level~~$date {$facility[$level]} $string" ) ."\";\n";
		++$i;
		if ( $level == 1 ) {
			$info = 1;
		} elseif ( $level == 2 ) {
			$warn = 1;
		} elseif ( $level == 4 ) {
			$error = 1;
		} elseif ( $level == 8 ) {
			$debug = 1;
			continue;
		}
		$logline .= "$date {$facility[$level]} $string";
	}
}
?>
</script>
<?php

if ( defined('CODE_PROFILER_TEXTAREA_HEIGHT') ) {
	$th = (int) CODE_PROFILER_TEXTAREA_HEIGHT;
} else {
	$th = '450';
}

echo code_profiler_display_tabs( 4 );
?>
<h3><?php esc_html_e('Code Profiler log', 'code-profiler'); ?></h3>
<form name="cplogform" method="post">
	<table class="form-table">
		<tr>
			<td width="100%">
				<textarea dir="auto" name="cptxtlog" class="large-text code" style="height:<?php echo (int)$th; ?>px;" wrap="off" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?php
				if (! empty( $logline ) ) {
					echo esc_textarea( $logline );
					$disabled = '';
				} else {
					echo "\n\n > " . esc_html__("Code Profiler's log is empty.", 'code-profiler');
					$disabled = ' disabled';
				}
				if (! $error ) {
					$error_checked = '';
				} else {
					$error_checked = ' checked';
				}
				if (! $warn ) {
					$warn_checked = '';
				} else {
					$warn_checked = ' checked';
				}
				?></textarea>
				<p style="text-align:center">
					<label for="cxinfo">
						<input id="cxinfo" type="checkbox" name="info" checked="checked"<?php disabled( $info, 0 ) ?> onClick="cpjs_filter_log();"<?php echo $disabled ?> />
						<span style="display: inline-block"><?php esc_html_e('Info', 'code-profiler') ?></span>&nbsp;
					</label>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label for="cxwarn">
						<input id="cxwarn" type="checkbox" name="warn"<?php echo $warn_checked ?><?php disabled( $warn, 0 ) ?> onClick="cpjs_filter_log();"<?php echo $disabled ?> />
						<span style="display: inline-block"><?php esc_html_e('Warning', 'code-profiler') ?></span>&nbsp;
					</label>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label for="cxerror">
						<input id="cxerror" type="checkbox" name="error"<?php echo $error_checked ?><?php disabled( $error, 0 ) ?> onClick="cpjs_filter_log();"<?php echo $disabled ?> />
						<span style="display: inline-block"><?php esc_html_e('Error', 'code-profiler') ?></span>&nbsp;
					</label>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label for="cxdebug">
						<input id="cxdebug" type="checkbox" name="debug"<?php disabled( $debug, 0 ) ?> onClick="cpjs_filter_log();"<?php echo $disabled ?> />
						<span style="display: inline-block"><?php esc_html_e('Debug', 'code-profiler') ?></span>&nbsp;
					</label>
				</p>
				<input type="submit" onclick="return cpjs_delete_log();" name="cp-delete-log" class="button button-primary" value="<?php esc_attr_e('Delete log', 'code-profiler') ?>"<?php disabled( $logline, '') ?> />
				<?php wp_nonce_field('cp_delete_log', 'cp_nonce', 0); ?>
				<p class="description"><?php esc_html_e('The log is deleted automatically when it reaches 100KB.', 'code-profiler') ?></p>
			</td>
		</tr>
	</table>
</form>

<h3><?php esc_html_e('HTTP response log', 'code-profiler'); ?></h3>
<table class="form-table">
	<tr>
		<td width="100%">
			<textarea dir="auto" name="cptxtlog" class="large-text code" style="height:<?php echo $th; ?>px;" wrap="off" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?php

			/**
			 * Search for an eventual "last_request.1727412303.5681.log" file.
			 */
			$file = code_profiler_glob( CODE_PROFILER_UPLOAD_DIR, '^last_request\.\d+?\.\d+?\.log$', true );
			if ( empty( $file[0] ) ) {
				echo "\n\n > " . esc_html__('The HTTP response log is empty.', 'code-profiler');
			} else {
				$log = file_get_contents( $file[0] );
				echo esc_textarea( $log );
			}
			?></textarea>
			<p class="description"><?php esc_html_e('The log shows the HTTP request and its payload, the response headers and body for the last profile.', 'code-profiler') ?></p>
		</td>
	</tr>
</table>
<?php

// =====================================================================
// EOF
