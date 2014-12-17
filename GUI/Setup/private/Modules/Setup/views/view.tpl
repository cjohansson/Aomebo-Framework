{**
 * view
 *
 * @var array $submit
 * @var array $tests
 **}
<div id="{$F}">
    <h2>{__('Configuration')}</h2>
    <div>
        {__('Fill in form below to test or export configuration.')}
    </div>
    <form action="" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset>
            <legend>{__('Database')}</legend>
            <div>
                <p>
                    <label for="database_host">{__('Host')}</label>
                </p>
                <input type="text" name="database_host" id="database_host" value="{$submit.database_host|escape}" />
            </div>
            <div>
                <p>
                    <label for="database_database">{__('Database')}</label>
                </p>
                <input type="text" name="database_database" id="database_database" value="{$submit.database_database|escape}" />
            </div>
            <div>
                <p>
                    <label for="database_username">{__('Username')}</label>
                </p>
                <input type="text" name="database_username" id="database_username" value="{$submit.database_username|escape}" />
            </div>
            <div>
                <p>
                    <label for="database_password">{__('Password')}</label>
                </p>
                <input type="text" name="database_password" id="database_password" value="{$submit.database_password|escape}" />
            </div>
        </fieldset>
        <div>
            <input type="submit" name="action" value="{__('Test')}" />
            <input type="submit" name="action" value="{__('Export configuration.php')}" />
        </div>
        {if sizeof($tests) > 0}
            <fieldset>
                <legend>{__('Tests')}</legend>
                {foreach $tests as $test}
                    <div>{$test|escape}</div>
                {/foreach}
            </fieldset>
        {/if}
    </form>
</div>
