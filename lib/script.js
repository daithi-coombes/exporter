$(document).ready(function(){


})

var Exporter = {

	total: 0,

	run: function(){

		$.post(
			ExporterURL,
			{
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