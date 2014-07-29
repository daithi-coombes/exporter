$(document).ready(function(){


})

var Exporter = {

	total: 0,

	run: function(){

		$.post(
			ExporterURL,
			{
				manufacturer_id: $('select[name=manufacturer_id]').val(),
				date: $('#date1').val(),
				_nonce: ExporterNonce,
				action: 'run_batch'
			},
			Exporter.parseResponse,
			'json'
		);
	},

	getTotal: function(){
		

	},

	parseResponse: function( res ){

		console.log(res);
	}
}