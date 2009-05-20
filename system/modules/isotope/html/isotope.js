/**
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
var Isotope = 
{
	toggleAddressFields: function(el, id)
	{
		if (el.value == '0' && el.checked)
		{
			$(id).setStyle('display', 'block');
		}
		else
		{
			$(id).setStyle('display', 'none');
		}
	}
};

