{**
 * view
 *}
{if $doctype == 'html 4.01 strict'}
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
{elseif $doctype == 'html 4.01 transitional'}
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
{elseif $doctype == 'html 4.01 frameset'}
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
{elseif $doctype == 'xhtml 1.0 strict'}
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
{elseif $doctype == 'xhtml 1.0 frameset'}
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
{elseif $doctype == 'xhtml 1.1'}
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
{elseif $doctype == 'html5'}
    <!DOCTYPE html>
{else}
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{/if}
{if $doctype_xml_based}
    <!--[if lt IE 7 ]><html  dir="ltr" lang="{$html_lang}" class="ie6 ltie7 ltie8 ltie9"><![endif]-->
    <!--[if IE 7 ]><html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="{$html_lang}" class="ie7 ltie8 ltie9"><![endif]-->
    <!--[if IE 8 ]><html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="{$html_lang}" class="ie8 ltie9"><![endif]-->
    <!--[if (gte IE 9)|!(IE)]><!--><html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="{$html_lang}"><!--<![endif]-->
{else}
    <!--[if lt IE 7 ]><html lang="{$html_lang}" dir="ltr" class="ie6 ltie7 ltie8 ltie9"><![endif]-->
    <!--[if IE 7 ]><html lang="{$html_lang}" dir="ltr" class="ie7" ltie8 ltie9><![endif]-->
    <!--[if IE 8 ]><html lang="{$html_lang}" dir="ltr" class="ie8 ltie9"><![endif]-->
    <!--[if (gte IE 9)|!(IE)]><!--><html dir="ltr" lang="{$html_lang}"><!--<![endif]-->
{/if}
    <head>
        {if is_array($meta)}
            {foreach $meta as $met}
                {$met}
            {/foreach}
        {/if}
        <script type="text/javascript">
            var _AJAX_URI_ = '{$ajax_uri}';
            var _PUBLIC_URI_ = '{$public_uri}';
            var _RESOURCES_URI_ = '{$R}';
            var _SCRIPT_DEPENDENCIES_URI_ = '{$script_dependencies_uri}';
            var _STYLE_DEPENDENCIES_URI_ = '{$style_dependencies_uri}';
            var _SCRIPT_ASSOCIATIVES_URI_ = '{$script_associatives_uri}';
            var _STYLE_ASSOCIATIVES_URI_ = '{$style_associatives_uri}';
        </script>
        <title>
            {$title}
        </title>
        {if !empty($page_data.canonical_uri)}
            <link rel="canonical" href="{$page_data.canonical_uri}" />
        {/if}
        {$shortcut_icon}
    </head>
    <body id="page-{$current_page}" class="{if $is_ios}ios{else}notios{/if}">
        {$body}
    </body>
</html>
