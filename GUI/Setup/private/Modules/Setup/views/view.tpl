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
            <div>
                <p>
                    <label for="database_type">{__('Type')}</label>
                </p>
                <select name="database_type" id="database_type">
                    <option value="mysqli"{if $submit.database_type == 'mysqli'} selected="selected"{/if}>{__('MySQLi')}</option>
                    <option value="pdo"{if $submit.database_type == 'pdo'} selected="selected"{/if}>{__('PDO')}</option>
                </select>
            </div>
            <div>
                <p>
                    <label for="database_dsn">{__('DSN')}</label>
                </p>
                <input type="text" name="database_dsn" id="database_dsn" value="{$submit.database_dsn|escape}" />
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
