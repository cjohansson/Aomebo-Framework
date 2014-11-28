{**
 * @var array|null $submit
 * @var string $locale
 * @var string $translated
 **}
<div id="{$F}">
    <h2>Setup template via Smarty</h2>
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
                <input type="text" name="paths_default_file_mod" id="paths_default_file_mod" value="{$submit.pathsDefaultFileMod|escape}" />
            </div>
        </fieldset>
        <fieldset>
            <legend>Site</legend>
            <div>
                <p>
                    <label for="site_title">Title</label>
                </p>
                <input type="text" name="site_title" id="site_title" value="{$submit.siteTitle|escape}" />
            </div>
            <div>
                <p>
                    <label for="site_title_delimiter">Title delimiter</label>
                </p>
                <input type="text" name="site_title_delimiter" id="site_title_delimiter" value="{$submit.siteTitleDelimiter|escape}" />
            </div>
            <div>
                <p>
                    <label for="site_title_direction">Title direction</label>
                </p>
                <select name="site_title_direction" id="site_title_direction">
                    <option value="prepend"{if $submit.siteTitleDirection == 'prepend'} selected="selected"{/if}>prepend</option>
                    <option value="append"{if $submit.siteTitleDirection == 'append'} selected="selected"{/if}>append</option>
                </select>
            </div>
            <div>
                <p>
                    <label for="site_slogan">{__('Slogan')}</label>
                </p>
                <input type="text" name="site_slogan" id="site_slogan" value="{$submit.siteSlogan|escape}" />
            </div>
            <div>
                <p>
                    <label for="site_internal_root">Public internal root (i.e. /var/www/mysite/public)</label>
                </p>
                <input type="text" name="site_internal_root" id="site_internal_root" value="{$submit.siteInternalRoot|escape}" />
            </div>
            <div>
                <p>
                    <label for="site_server_name">{__('Public external root (i.e. example.org)')}</label>
                </p>
                <input type="text" name="site_server_name" id="site_server_name" value="{$submit.siteServerName|escape}" />
            </div>
            <div>
                From database cache: "{$cache}"
            </div>
            <div>
                Localized "Invalid parameters" to locale "{$locale|escape}": "{__('Invalid parameters')}"
            </div>
            <div>
                Localized2: "{$translated|escape}"
            </div>
            <div>
                <strong>{__('Routes examples:')}</strong>
                <ul>
                    <li>
                        {__('Route 1:')}
                        <br /><a href="{url page='about'}">{__('About')}</a>
                    </li>
                    <li>
                        {__('Route 2:')}
                        <br /><a href="{url parameter1='save' parameter2='settings'}">{__("Save settings")}</a>
                    </li>
                    <li>
                        {__('Route 3:')}
                        <br /><a href="{url parameter1='save' parameter2='settings' parameter3='confirm'}">{__("Confirm save settings")}</a>
                    </li>
                </ul>
            </div>
        </fieldset>
        <div>
            <input type="submit" value="Save" />
        </div>
    </form>
</div>
