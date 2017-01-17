<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/common_admin.php';


if ($pun_user['g_id'] != PUN_ADMIN)
	message($lang_common['No permission'], false, '403 Forbidden');

// Load the admin_permissions.php language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_permissions.php';

$request = $container->get('Request');

if ($request->isPost('form_sent'))
{
	confirm_referrer('admin_permissions.php');

	$form = array_map('intval', $request->post('form', array()));

	foreach ($form as $key => $input)
	{
		// Make sure the input is never a negative value
		if($input < 0)
			$input = 0;

		// Only update values that have changed
		if (array_key_exists('p_'.$key, $pun_config) && $pun_config['p_'.$key] != $input)
			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$input.' WHERE conf_name=\'p_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
	}

	$container->get('config update');

	redirect('admin_permissions.php', $lang_admin_permissions['Perms updated redirect']);
}

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_admin_common['Admin'], $lang_admin_common['Permissions']);
define('PUN_ACTIVE_PAGE', 'admin');
require PUN_ROOT.'header.php';

generate_admin_menu('permissions');

?>
	<div class="blockform">
		<h2><span><?php echo $lang_admin_permissions['Permissions head'] ?></span></h2>
		<div class="box">
			<form method="post" action="admin_permissions.php">
				<p class="submittop"><input type="submit" name="save" value="<?php echo $lang_admin_common['Save changes'] ?>" /></p>
				<div class="inform">
					<input type="hidden" name="form_sent" value="1" />
					<input type="hidden" name="csrf_hash" value="<?php echo csrf_hash() ?>" />
					<fieldset>
						<legend><?php echo $lang_admin_permissions['Posting subhead'] ?></legend>
						<div class="infldset">
							<table class="aligntop">
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['BBCode label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[message_bbcode]" value="1"<?php if ($pun_config['p_message_bbcode'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[message_bbcode]" value="0"<?php if ($pun_config['p_message_bbcode'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['BBCode help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['Image tag label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[message_img_tag]" value="1"<?php if ($pun_config['p_message_img_tag'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[message_img_tag]" value="0"<?php if ($pun_config['p_message_img_tag'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['Image tag help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['All caps message label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[message_all_caps]" value="1"<?php if ($pun_config['p_message_all_caps'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[message_all_caps]" value="0"<?php if ($pun_config['p_message_all_caps'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['All caps message help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['All caps subject label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[subject_all_caps]" value="1"<?php if ($pun_config['p_subject_all_caps'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[subject_all_caps]" value="0"<?php if ($pun_config['p_subject_all_caps'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['All caps subject help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['Require e-mail label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[force_guest_email]" value="1"<?php if ($pun_config['p_force_guest_email'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[force_guest_email]" value="0"<?php if ($pun_config['p_force_guest_email'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['Require e-mail help'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_permissions['Signatures subhead'] ?></legend>
						<div class="infldset">
							<table class="aligntop">
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['BBCode sigs label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[sig_bbcode]" value="1"<?php if ($pun_config['p_sig_bbcode'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[sig_bbcode]" value="0"<?php if ($pun_config['p_sig_bbcode'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['BBCode sigs help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['Image tag sigs label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[sig_img_tag]" value="1"<?php if ($pun_config['p_sig_img_tag'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[sig_img_tag]" value="0"<?php if ($pun_config['p_sig_img_tag'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['Image tag sigs help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['All caps sigs label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[sig_all_caps]" value="1"<?php if ($pun_config['p_sig_all_caps'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[sig_all_caps]" value="0"<?php if ($pun_config['p_sig_all_caps'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['All caps sigs help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['Max sig length label'] ?></th>
									<td>
										<input type="text" name="form[sig_length]" size="5" maxlength="5" value="<?php echo $pun_config['p_sig_length'] ?>" />
										<span class="clearb"><?php echo $lang_admin_permissions['Max sig length help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['Max sig lines label'] ?></th>
									<td>
										<input type="text" name="form[sig_lines]" size="3" maxlength="3" value="<?php echo $pun_config['p_sig_lines'] ?>" />
										<span class="clearb"><?php echo $lang_admin_permissions['Max sig lines help'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_permissions['Registration subhead'] ?></legend>
						<div class="infldset">
							<table class="aligntop">
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['Banned e-mail label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[allow_banned_email]" value="1"<?php if ($pun_config['p_allow_banned_email'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[allow_banned_email]" value="0"<?php if ($pun_config['p_allow_banned_email'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['Banned e-mail help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_permissions['Duplicate e-mail label'] ?></th>
									<td>
										<label class="conl"><input type="radio" name="form[allow_dupe_email]" value="1"<?php if ($pun_config['p_allow_dupe_email'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong></label>
										<label class="conl"><input type="radio" name="form[allow_dupe_email]" value="0"<?php if ($pun_config['p_allow_dupe_email'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong></label>
										<span class="clearb"><?php echo $lang_admin_permissions['Duplicate e-mail help'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="save" value="<?php echo $lang_admin_common['Save changes'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
