(function ($, Drupal, webauthn) {
  Drupal.behaviors.webauthn = {
    attach: function (context, settings) {

      $('body').once('webauthn').each(function () {
        const login = webauthn.useLogin({
          actionUrl: '/webauthn/login/action',
          optionsUrl: '/webauthn/login/options'
        });

        const register = webauthn.useRegistration({
          actionUrl: '/webauthn/register/action',
          optionsUrl: '/webauthn/register/options'
        });

        $('#webauthn-register').submit(function (event) {
          event.preventDefault();

          register({
            username: $('#edit-username', this).val(),
            displayName: $('#edit-username', this).val()
          })
          .then(function (response) {
            console.log('Registration success')
            console.log(response);
          })
          .catch(function (error) {
            console.log('Registration failure')
            console.log(error);
          });
        });

        $('#webauthn-login').submit(function (event) {
          event.preventDefault();

          login({
            username: $('#edit-username', this).val()
          })
          .then(function (response) {
            console.log('Authentication success')
            console.log(response);
          })
          .catch(function (error) {
            console.log('Authentication failure')
            console.log(error);
          });
        });

      });

    }
  };
})(jQuery, Drupal, webauthn);
