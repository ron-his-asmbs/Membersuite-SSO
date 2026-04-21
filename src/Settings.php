<?php

namespace ASMBS\SSO;

class Settings
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
    }

    public function addMenuPage(): void
    {
        add_options_page(
            'ASMBS SSO',
            'ASMBS SSO',
            'manage_options',
            'asmbs-sso',
            [$this, 'renderPage']
        );
    }

    public function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $user        = null;
        $msData      = null;
        $searchError = null;
        $saveSuccess = false;

        // Handle meta update form submission
        if (isset($_POST['asmbs_sso_update']) && check_admin_referer('asmbs_sso_update')) {
            $wp_user_id = intval($_POST['wp_user_id']);
            update_user_meta($wp_user_id, 'mem_guid', sanitize_text_field($_POST['mem_guid']));
            update_user_meta($wp_user_id, 'mem_key', sanitize_text_field($_POST['mem_key']));
            update_user_meta($wp_user_id, 'mem_status', sanitize_text_field($_POST['mem_status']));
            update_user_meta($wp_user_id, 'mem_type', sanitize_text_field($_POST['mem_type']));
            $saveSuccess = true;
            $user = get_user_by('id', $wp_user_id);
        }

        // Handle user search
        if (!empty($_GET['search']) && !$user) {
            $search = sanitize_text_field($_GET['search']);

            // Try email first
            $user = get_user_by('email', $search);

            // Try login
            if (!$user) {
                $user = get_user_by('login', $search);
            }

            // Try mem_guid
            if (!$user) {
                $users = get_users(['meta_key' => 'mem_guid', 'meta_value' => $search, 'number' => 1]);
                $user  = $users[0] ?? null;
            }

            // Try mem_key
            if (!$user) {
                $users = get_users(['meta_key' => 'mem_key', 'meta_value' => $search, 'number' => 1]);
                $user  = $users[0] ?? null;
            }

            if (!$user) {
                $searchError = 'No user found matching "' . esc_html($search) . '"';
            }
        }

        // Handle live MemberSuite lookup
        if ($user && isset($_GET['ms_lookup'])) {
            try {
                $sso    = new SSO();
                $guid   = get_user_meta($user->ID, 'mem_guid', true);
                if (!empty($guid)) {
                    $msData = $sso->lookupByGuid($guid);
                } else {
                    $searchError = 'No MemberSuite GUID found for this user — cannot perform live lookup.';
                }
            } catch (\Exception $e) {
                $searchError = 'MemberSuite lookup failed: ' . $e->getMessage();
            }
        }

        ?>
        <div class="wrap">
            <h1>ASMBS SSO — User Lookup</h1>

            <!-- Search Form -->
            <form method="get">
                <input type="hidden" name="page" value="asmbs-sso">
                <table class="form-table">
                    <tr>
                        <th>Search User</th>
                        <td>
                            <input
                                type="text"
                                name="search"
                                value="<?php echo esc_attr($_GET['search'] ?? ''); ?>"
                                placeholder="Email, username, GUID, or local ID"
                                class="regular-text"
                            />
                            <button type="submit" class="button button-primary">Search</button>
                        </td>
                    </tr>
                </table>
            </form>

            <?php if ($searchError) : ?>
                <div class="notice notice-error"><p><?php echo esc_html($searchError); ?></p></div>
            <?php endif; ?>

            <?php if ($saveSuccess) : ?>
                <div class="notice notice-success"><p>User meta updated successfully.</p></div>
            <?php endif; ?>

            <?php if ($user) : ?>
                <hr>
                <h2><?php echo esc_html($user->display_name); ?> (<?php echo esc_html($user->user_email); ?>)</h2>
                <p>
                    <strong>WP User ID:</strong> <?php echo esc_html($user->ID); ?> &nbsp;|&nbsp;
                    <strong>Role:</strong> <?php echo esc_html(implode(', ', $user->roles)); ?> &nbsp;|&nbsp;
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'asmbs-sso', 'search' => $_GET['search'] ?? '', 'ms_lookup' => 1])); ?>" class="button button-secondary">
                        Live MemberSuite Lookup
                    </a>
                    <a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>" class="button button-secondary">
                        Edit WP User
                    </a>
                </p>

                <!-- Meta Edit Form -->
                <form method="post">
                    <?php wp_nonce_field('asmbs_sso_update'); ?>
                    <input type="hidden" name="wp_user_id" value="<?php echo esc_attr($user->ID); ?>">
                    <input type="hidden" name="asmbs_sso_update" value="1">
                    <h3>MemberSuite Meta</h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="mem_guid">GUID (mem_guid)</label></th>
                            <td>
                                <input type="text" id="mem_guid" name="mem_guid" class="regular-text"
                                    value="<?php echo esc_attr(get_user_meta($user->ID, 'mem_guid', true)); ?>"/>
                                <p class="description">MemberSuite individual GUID</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="mem_key">Local ID (mem_key)</label></th>
                            <td>
                                <input type="text" id="mem_key" name="mem_key" class="regular-text"
                                    value="<?php echo esc_attr(get_user_meta($user->ID, 'mem_key', true)); ?>"/>
                                <p class="description">MemberSuite local numeric ID</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="mem_status">Status (mem_status)</label></th>
                            <td>
                                <input type="text" id="mem_status" name="mem_status" class="regular-text"
                                    value="<?php echo esc_attr(get_user_meta($user->ID, 'mem_status', true)); ?>"/>
                                <p class="description">e.g. A, N, I, E</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="mem_type">Type (mem_type)</label></th>
                            <td>
                                <input type="text" id="mem_type" name="mem_type" class="regular-text"
                                    value="<?php echo esc_attr(get_user_meta($user->ID, 'mem_type', true)); ?>"/>
                                <p class="description">e.g. MD, IH, CM, IN</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Save Meta'); ?>
                </form>

                <?php if ($msData) : ?>
                    <hr>
                    <h3>Live MemberSuite Data</h3>
                    <pre style="background:#f6f7f7; padding:15px; overflow:auto;"><?php echo esc_html(json_encode($msData, JSON_PRETTY_PRINT)); ?></pre>
                <?php endif; ?>

            <?php endif; ?>
        </div>
        <?php
    }
}
