<?php
/**
 * @var array|null $submit
 * @var string $locale
 * @var string $F
 * @var string $cache
 */
 ?>
<div id="<?php echo $this->F; ?>">
    <h2>Setup template via PHP</h2>
    <div>
        Fill in form below to setup a Aomebo Framework system.
    </div>
    <form action="" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset>
            <legend>Paths</legend>
            <div>
                <p>
                    <label for="paths_default_file_mod">Default file mod</label>
                </p>
                <input type="text" name="paths_default_file_mod" id="paths_default_file_mod" value="<?php echo (isset($this->submit['pathsDefaultFileMod']) ? $this->submit['pathsDefaultFileMod'] : ''); ?>" />
            </div>
        </fieldset>
        <fieldset>
            <legend>Site</legend>
            <div>
                <p>
                    <label for="site_title">Title</label>
                </p>
                <input type="text" name="site_title" id="site_title" value="<?php echo (isset($submit['siteTitle']) ? $submit['siteTitle'] : ''); ?>" />
            </div>
            <div>
                <p>
                    <label for="site_title_delimiter">Title delimiter</label>
                </p>
                <input type="text" name="site_title_delimiter" id="site_title_delimiter" value="<?php echo (isset($this->submit['siteTitleDelimiter']) ? $this->submit['siteTitleDelimiter'] : ''); ?>" />
            </div>
            <div>
                <p>
                    <label for="site_title_direction">Title direction</label>
                </p>
                <select name="site_title_direction" id="site_title_direction">
                    <option value="prepend"<?php echo (isset($this->submit['siteTitleDirection']) && $this->submit['siteTitleDirection'] == 'prepend' ? ' selected="selected"' : ''); ?>>prepend</option>
                    <option value="append"<?php echo (isset($this->submit['siteTitleDirection']) && $this->submit['siteTitleDirection'] == 'append' ? ' selected="selected"' : ''); ?>>append</option>
                </select>
            </div>
            <div>
                <p>
                    <label for="site_slogan">Slogan</label>
                </p>
                <input type="text" name="site_slogan" id="site_slogan" value="<?php echo (isset($this->submit['siteSlogan']) ? $this->submit['siteSlogan'] : ''); ?>" />
            </div>
            <div>
                <p>
                    <label for="site_internal_root">Public internal root (i.e. /var/www/mysite/public)</label>
                </p>
                <input type="text" name="site_internal_root" id="site_internal_root" value="<?php echo (isset($this->submit['siteInternalRoot']) ? $this->submit['siteInternalRoot'] : ''); ?>" />
            </div>
            <div>
                <p>
                    <label for="site_server_name">Public external root (i.e. example.org)</label>
                </p>
                <input type="text" name="site_server_name" id="site_server_name" value="<?php echo (isset($this->submit['siteInternalRoot']) ? $this->submit['siteServerName'] : ''); ?>" />
            </div>
            <div>
                From database cache: "<?php echo $this->cache; ?>"
            </div>
            <div>
                Localized "Invalid parameters" to locale "<?php echo $this->locale; ?>": "<?php echo __('Invalid parameters'); ?>"
            </div>
            <div>
                Localized2: "<?php echo $this->translated; ?>"
            </div>
            <div>
                <strong><?php _e('Routes examples:'); ?></strong>
                <ul>
                    <li>
                        <?php _e('Route 1:'); ?>
                        <br /><a href="<?php echo $this->uri(array('page' => 'about')); ?>"><?php _e('About'); ?></a>
                    </li>
                    <li>
                        <?php _e('Route 2:'); ?>
                        <br /><a href="<?php echo $this->uri(array('parameter1' => 'save', 'parameter2' => 'settings')); ?>"><?php _e('Save settings'); ?></a>
                    </li>
                    <li>
                        <?php _e('Route 3:'); ?>
                        <br /><a href="<?php echo $this->uri(array('parameter1' => 'save', 'parameter2' => 'settings', 'parameter3' => 'confirm')); ?>"><?php _e('Confirm save settings'); ?></a>
                    </li>
                </ul>
            </div>
        </fieldset>
        <fieldset>
            <legend><?php _e('Database'); ?></legend>
            <div>
                <strong><?php _e('Database tests:'); ?></strong>
                <br /><?php _e($this->databaseTests); ?>
            </div>
        </fieldset>
        <div>
            <input type="submit" value="Save" />
        </div>
    </form>
</div>
