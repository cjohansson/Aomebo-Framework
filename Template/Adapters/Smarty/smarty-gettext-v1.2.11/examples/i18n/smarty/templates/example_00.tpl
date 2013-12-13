Almost nothing to do, but 'gettexting' your way :). 

When can you use this example?
  -- if you bind/set your text-domain from within your PHP application 
  -- if you just use ONE text-domain for ALL TEMPLATES (does not matter if you have multiple for the rest of your PHP application)
  -- if you make sure that the right textdomain is set and bound when you call fetch/display the template

RUNNING THE EXAMPLE:
php example_00.php [locale]

{_("This is my first translation message")}
{"This is my yet another translation message with a custom modifier"|gettext|substr:0:60}
{"%d comment"|ngettext:"%d comments":1|sprintf:1}
{"%d comment"|ngettext:"%d comments":0|sprintf:0}
{"%d comment"|ngettext:"%d comments":10|sprintf:10}
