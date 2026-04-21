<?php

namespace ASMBS\SSO;

class Shortcodes
{
    public function __construct()
    {
        add_shortcode('asmbs_sso_form', [$this, 'renderSSOForm']);
    }

    public function renderSSOForm(): string
    {
        $nextUrl       = esc_url(site_url('/nexturl') . '?returnTo=' . urlencode(get_permalink()));
        $associationId = esc_attr($_ENV['MS_ASSOCIATION_ID'] ?? '');

        ob_start(); ?>
        <form id="reverse-sso-form"
              method="POST"
              action="https://rest.membersuite.com/platform/v2/signUpSSO"
              style="display:none">
            <input type="hidden" name="nextURL" value="<?php echo $nextUrl; ?>">
            <input type="hidden" name="IsSignUp" value="false">
            <input type="hidden" name="AssociationId" value="<?php echo $associationId; ?>">
            <input type="hidden" name="isReverseSSO" value="true">
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.js-reverse-sso').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var form = document.getElementById('reverse-sso-form');
                    if (form) {
                        form.submit();
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
