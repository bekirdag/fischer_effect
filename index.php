<?php

require_once 'simplehtmldom/simple_html_dom.php';

$html = new simple_html_dom();

$html->load_file('http://www.tradingeconomics.com/country-list/interest-rate');

$try_rate = trim($html->find('a[href=/turkey/interest-rate]',0)->parent()->parent()->children(1)->innertext); 
$usd_rate = trim($html->find('a[href=/united-states/interest-rate]',0)->parent()->parent()->children(1)->innertext);
$eur_rate = trim($html->find('a[href=/euro-area/interest-rate]',0)->parent()->parent()->children(1)->innertext);
$rub_rate = trim($html->find('a[href=/russia/interest-rate]',0)->parent()->parent()->children(1)->innertext);
$aud_rate = trim($html->find('a[href=/australia/interest-rate]',0)->parent()->parent()->children(1)->innertext);
$uah_rate = trim($html->find('a[href=/ukraine/interest-rate]',0)->parent()->parent()->children(1)->innertext);
$gbp_rate = trim($html->find('a[href=/united-kingdom/interest-rate]',0)->parent()->parent()->children(1)->innertext);

if(isset($_GET['current_rate']))
{
  $current_rate = $_GET['current_rate'];
  
  $id = $_GET['id'];
  $if = $_GET['if'];
  $n = $_GET['n']/365;
  
  $f = $current_rate * pow(((1+$id/100)/(1+$if/100)),$n);
  
  print "<div style=''> After " . $_GET["n"] . " days 1 " . $_GET["cur1"] . " will be " .$f." " . $_GET["cur2"] ."</div>";
  
}

$interest_rates = array(
    "TRY"=>$try_rate,
    "USD"=>$usd_rate,
    "EUR"=>$eur_rate,
    "RUB"=>$rub_rate,
    "AUD"=>$aud_rate,
    "UAH"=>$uah_rate,
    "GBP"=>$gbp_rate,
); 

?>

<!DOCTYPE HTML>
<html lang="en-EN">
<head>
	<meta charset="UTF-8">
	<title>Currency estimation</title>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="js/money.js"></script>
	<script src="js/Chart.js"></script>
	<script src="js/moment.js"></script>
</head>

<script type="text/javascript">

  var interest_rates = <?php print json_encode($interest_rates)?>;
//   console.log(interest_rates);

  jQuery(document).ready(function($) {
	  set_exchange_rate();
	  set_interest_rates();

	  

		$(".currency").on("change",function(){
			set_exchange_rate();
			set_interest_rates();
		});

		$(".currency_input").on("change",function(){
			set_chart();
		});
  });

  function set_exchange_rate(){
	  var cur1 = $("#cur1").val();
	  var cur2 = $("#cur2").val();

	  var demo = function() {
		  var rate = fx(1).from(cur1).to(cur2)
		  $("#current_rate").val(rate.toFixed(4));
		  set_chart();
		}

		var req = new XMLHttpRequest()
		req.onreadystatechange = function() {
		  if (req.readyState == 4) {
		    var data = JSON.parse(req.responseText)
		    fx.rates = data.rates;
		    fx.rates[cur1] = 1;
// 		    console.log(fx.rates);
		    demo();
		  }
		}
		req.open("GET", "http://api.fixer.io/latest?base="+cur1);
		req.send();
  }

  function set_interest_rates()
  {
	  var cur1 = $("#cur1").val();
	  var cur2 = $("#cur2").val();
	  
	  $("#did").val(interest_rates[cur2]);
	  $("#fif").val(interest_rates[cur1]);
  }

  function set_chart()
  {
	  $("#myChart").remove();
	  $("body").append('<canvas id="myChart" width="600" height="400"></canvas>');
	  var ctx = document.getElementById("myChart").getContext("2d");

	  var chart_data = get_points(); 
	  var points = chart_data["data"];
	  var labels= chart_data["labels"];
	  
	  var data = {
			    labels: labels,
			    datasets: [
			        {
			            label: "Exchange rate assumption",
			            fillColor: "rgba(220,220,220,0.2)",
			            strokeColor: "rgba(220,220,220,1)",
			            pointColor: "rgba(220,220,220,1)",
			            pointStrokeColor: "#fff",
			            pointHighlightFill: "#fff",
			            pointHighlightStroke: "rgba(220,220,220,1)",
			            data: points
			        }
			    ]
			};

	  var myLineChart = new Chart(ctx).Line(data);
  }

  function get_points()
  {
	  var days = $("#n").val();
	  var result = [];
	  var labels = [];
	  for (i = 0; i < days; i++) { 
		  
		  var current_rate = $("#current_rate").val();
		  
		  var did = $("#did").val();
		  var fif = $("#fif").val();
		  var n = i/365;

		  
		  if(i%30==0)
		  {
  		  var f = current_rate * Math.pow(((1+did/100)/(1+fif/100)),n);
  		  f = f.toFixed(3);
  
  		  result.push(f);
		  
			  var cur = new Date(),
			    afteridays = cur.setDate(cur.getDate() + i);

			  var the_date = moment(afteridays);
			  var formatted = the_date.format("M/YY"); 
			  
			  labels.push(formatted);
		  }
		  
	  }

	  var data = {"data":result,"labels":labels};
	  
	  return data;
	}
	
</script>

<style>
body{
  font-family: 'Open Sans', sans-serif;
}

tr{
  padding:10px;
}

</style>

<body>
  <div id="notice" style="font-style:italic;color:#888;font-size:10px;padding:10px;">This tool uses <a href="https://en.wikipedia.org/wiki/International_Fisher_effect" target = "_blank">international Fisher effect</a> hypothesis to calculate forward exchange rates.</div>
	<div id="currency_estimation">
  	<form method="get">
  		<table>
  		  <tr>
  				<td>Currencies</td>
  				<td>
  				  <select name="cur1" id="cur1" class="currency">
  				    <option value="USD" selected>USD</option>
  				    <option value="TRY">TRY</option>
  				    <option value="GBP">GBP</option>
  				    <option value="EUR">EUR</option>
<!--   				    <option value="UAH">UAH</option> -->
  				    <option value="RUB">RUB</option>
  				  </select>
  				  
  				  <select name="cur2" id="cur2" class="currency">
  				    <option value="USD">USD</option>
  				    <option value="TRY" selected>TRY</option>
  				    <option value="GBP">GBP</option>
  				    <option value="EUR">EUR</option>
<!--   				    <option value="UAH">UAH</option> -->
  				    <option value="RUB">RUB</option>
  				  </select>
  				</td>
  				<td style="font-style:italic;"> </td>
  			</tr>
  			<tr>
  				<td>Current rate</td>
  				<td><input name="current_rate" id ="current_rate" type="text"  class="currency_input"/></td>
  				<td style="font-style:italic;"> </td>
  			</tr>
  			<tr>
  				<td>Domestic interest rate</td>
  				<td><input name="id" id="did" type="text" class="currency_input"/></td>
  			</tr>
  			<tr>
  				<td>Foreign interest rate</td>
  				<td><input name="if" id="fif" type="text" class="currency_input"/></td>
  			</tr>
  			<tr>
  				<td>Days</td>
  				<td><input name="n" id="n" type="text" value="365" class="currency_input"/></td>
  			</tr>
  			<tr>
  				<td><input type="submit" style="display:none;"/></td>
  			</tr>
  		</table>
  	</form>
  </div>
  
  <canvas id="myChart" width="600" height="400"></canvas>
</body>
</html>



