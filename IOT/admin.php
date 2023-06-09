
<?php
    include_once('esp-database.php');

    $result = getAllOutputs();
    $html_buttons = null;
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row["state"] == "1"){
                $button_checked = "checked";
            }
            else {
                $button_checked = "";
            }
            $html_buttons .= '<h3>' . $row["name"] . ' - GPIO ' . $row["gpio"] . ' (<i><a onclick="deleteOutput(this)" href="javascript:void(0);" id="' . $row["id"] . '">Delete</a></i>)</h3><label class="switch"><input type="checkbox" onchange="updateOutput(this)" id="' . $row["id"] . '" ' . $button_checked . '><span class="slider"></span></label>';
        }
    }
?>

<!DOCTYPE HTML>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="css/esp-style.css">
        <title>ESP Output Control</title>
		
		<style>
		.footer {
		   position: fixed;
		   left: 0;
		   bottom: 0;
		   width: 100%;
		   background-color: #efefef;;
		   color: black;
		   text-align: center;
		}
		</style>
		
    </head>
<body>
    <h2>Switch Control</h2>
	
    <?php echo $html_buttons; ?>
    <br><br>
	<div>
		<h4>Switch State Changing History :</h4>
		<?php 
			include_once('esp-database.php');

			$result = getHistory();
			if ($result) {
				while ($row = $result->fetch_assoc()) {
					if ($row["State"] == "1"){
						$changed = "Turned On";
					}
					else {
						$changed = "Turned OFF";
					}
					echo '<h5>' . $row["User"] . ' &nbsp&nbsp' . $changed . '&nbsp&nbsp Switch ' . $row["gpio"] . '&nbsp&nbsp at ' . $row["last_request"] . '</h5>';
				}
			}
		 ?>
	 </div>
	 <br><br><hr><br><br>
	
    <div><form onsubmit="return createDelay();">
        <h3>Set Delay of Alarm</h3>
        <label for="outputdelay">Delay (Seconds)</label>
        <input type="number" name="delay" min="0" id="outputdelay">
        <input type="submit" value="Set Delay">
    </form>
	
	<br><hr><br>
	
	<form onsubmit="return createOutput();">
        <h3>Create New Output</h3>
        <label for="outputName">Name</label>
        <input type="text" name="name" id="outputName"><br>
        <label for="outputGpio">GPIO (NodeMCU Output Pin Number)</label>
        <input type="number" name="gpio" min="0" id="outputGpio">
        <label for="outputState">Initial GPIO State</label>
        <select id="outputState" name="state">
          <option value="0">0 = OFF</option>
          <option value="1">1 = ON</option>
        </select>
        <input type="submit" value="Create Output">
        <p><strong>Note:</strong> in some devices, you might need to refresh the page to see your newly created buttons or to remove deleted buttons.</p>
    </form></div>


    <script>
        function updateOutput(element) {
            var xhr = new XMLHttpRequest();
            if(element.checked){
                xhr.open("GET", "esp-outputs-action.php?action=output_update&id="+element.id+"&state=1", true);
            }
            else {
                xhr.open("GET", "esp-outputs-action.php?action=output_update&id="+element.id+"&state=0", true);
            }
            xhr.send();
        }
		
		function createDelay(element) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "esp-outputs-action.php", true);

            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    alert("Delay Changed");
                    setTimeout(function(){ window.location.reload(); });
                }
            }
            var delayTime = document.getElementById("outputdelay").value;
            var httpRequestData = "action=output_delay&delay="+delayTime;
            xhr.send(httpRequestData);
        }

        function deleteOutput(element) {
            var result = confirm("Want to delete this output?");
            if (result) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "esp-outputs-action.php?action=output_delete&id="+element.id, true);
                xhr.send();
                alert("Output deleted");
                setTimeout(function(){ window.location.reload(); });
            }
        }

        function createOutput(element) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "esp-outputs-action.php", true);

            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    alert("Output created");
                    setTimeout(function(){ window.location.reload(); });
                }
            }
            var outputName = document.getElementById("outputName").value;
            var outputGpio = document.getElementById("outputGpio").value;
            var outputState = document.getElementById("outputState").value;
            var httpRequestData = "action=output_create&name="+outputName+"&gpio="+outputGpio+"&state="+outputState;
            xhr.send(httpRequestData);
        }
    </script>
<div class="footer">
  <p>created by Tharindu Sathsara <br> https://www.fiverr.com/tshome <br> tharindusathsarahome@gmail.com</p><br><br>
</div>
<br><br><br><br><br>
<p>-</p>
<br><br>
</body>
</html>