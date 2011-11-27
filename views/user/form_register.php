		<script type="text/javascript">
			var RecaptchaOptions = {
				theme : 'custom',
				custom_theme_widget: 'recaptcha_widget'
			};
		</script>



		<?php echo form_open('user/register'); ?>

		<?php echo validation_errors(); ?>

		<label for="username">Username</label>
		<input type="text" name="username" />

		<label for="email">Email</label>
		<input type="email" name="email" />

		<label for="password">Password</label>
		<input type="password" name="password"/>

		<label for="confirm">Confirm</label>
		<input type="password" name="confirm"/>


<!--
		<div id="recaptcha_widget">
			<label class="recaptcha_only_if_image">Enter the words below</label>
			<label class="recaptcha_only_if_audio">Enter the words you hear</label>

			<div id="recaptcha_image" style=""></div>
			<div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>


			<input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />

			<div class="recaptcha_only_if_image">
				<a href="javascript:Recaptcha.switch_type('audio')" class="button">Get an audio CAPTCHA</a>
			</div>
			<div class="recaptcha_only_if_audio">
				<a href="javascript:Recaptcha.reload()" class="button">Get another CAPTCHA</a>
				<a href="javascript:Recaptcha.switch_type('image')" class="button">Get an image CAPTCHA</a>
			</div>

		</div>
		<?php //echo recaptcha_get_html(); ?>
-->
		By registering you agree to the HostLaunch Terms of Service.

		<div id="links">
			<span style="float:right;">
				<button type="submit">Register</button>
			</span>
		</div>

		</form>