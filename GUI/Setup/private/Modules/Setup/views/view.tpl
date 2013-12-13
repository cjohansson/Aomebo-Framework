{**
 * @var array|null $submit
 **}
<div id="{$F}">
    <h2>Setup</h2>
    <div>
        Fill in form below to setup a Aomebo Framework system.
    </div>
    <form action="" method="post" enctype="application/x-www-form-urlencoded">
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
                    <label for="site_slogan">Slogan</label>
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
                    <label for="site_server_name">Public external root (i.e. example.org)</label>
                </p>
                <input type="text" name="site_server_name" id="site_server_name" value="{$submit.siteServerName|escape}" />
            </div>
        </fieldset>
        <div>
            <input type="submit" value="Save" />
        </div>
    </form>
    <div>
        Testing "{'öleof  KEO !? fkaple'|asciiEncode}"
    </div>
</div>
