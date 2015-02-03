
var datePicker = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );
};

datePicker.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
datePicker.prototype.constructor = datePicker;

datePicker.prototype.attachEvents = function()
{
    var self = this;

    this.element.datepicker(
		{
			dateFormat: 'dd/mm/yy',
			onSelect: function() { self.valueChanged(); }
		}
	);
};

datePicker.prototype.getDate = function()
{
	var date = this.element.datepicker( 'getDate' );

	var d = new Date( date.getFullYear(), date.getMonth(), date.getDate() );
	d.setTime( d.getTime() + (-date.getTimezoneOffset()*60*1000) );

	return d;
};

datePicker.prototype.setDate = function( date )
{

    this.element.datepicker( 'setDate', date );

	this.valueChanged();
};

window.gcd.core.mvp.viewBridgeClasses.DatePicker = datePicker;