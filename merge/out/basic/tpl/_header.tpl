	<!-- ----------------------------------------------------------------------------------------------- -->
	<!-- --------------------- script funcijas galima sumesti i atskira js failiuka -------------------- -->
	<!-- ----------------------------------------------------------------------------------------------- -->
	<script type="text/javascript">
		UTF8 = {
			encode: function(s){
				for(var c, i = -1, l = (s = s.split("")).length, o = String.fromCharCode; ++i < l;
					s[i] = (c = s[i].charCodeAt(0)) >= 127 ? o(0xc0 | (c >>> 6)) + o(0x80 | (c & 0x3f)) : s[i]
				);
				return s.join("");
			},
			decode: function(s){
				for(var a, b, i = -1, l = (s = s.split("")).length, o = String.fromCharCode, c = "charCodeAt"; ++i < l;
					((a = s[i][c](0)) & 0x80) &&
					(s[i] = (a & 0xfc) == 0xc0 && ((b = s[i + 1][c](0)) & 0xc0) == 0x80 ?
					o(((a & 0x03) << 6) + (b & 0x3f)) : o(128), s[++i] = "")
				);
				return s.join("");
			}
		};

		function showResult(str)
		{			
			if (str.length<=2)
			{
				document.getElementById("search_drop").style.display="none";				
				return;
			}			
			
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
		
			xmlhttp.onreadystatechange=function()
			{				
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{					
					$('.frame table').remove();					
					$('.frame div').remove();					
					var container = document.getElementById("frame"); 
					var newdiv = document.createElement("div"); 
					newdiv.setAttribute("id", "search_table");
					newdiv.innerHTML = xmlhttp.responseText; 					
					container.appendChild(newdiv);										
					document.getElementById('live_search_drop').href = document.getElementById('live_search_drop').href + str;					
					document.getElementById("search_drop").style.display="";
				}
			}
			if (navigator.userAgent.indexOf("MSIE") != -1){
				xmlhttp.open("GET","[{ $oViewConf->getBaseDir() }]index.php?cl=search_drop&searchparam="+UTF8.encode(str),true);
			}else{
				xmlhttp.open("GET","[{ $oViewConf->getBaseDir() }]index.php?cl=search_drop&searchparam="+UTF8.decode(str),true);
			}
			
			xmlhttp.send();
		}

		function setSearchDiv() 
		{
			var left = document.body.clientWidth / 2 - 300;
			document.getElementById('search_drop').style.left = left + "px";
		}
		
	</script>
	<!-- ----------------------------------------------------------------------------------------------- -->
	<!-- --------------------- apskaiciuoja div'o vieta pagal horizontale ------------------------------ -->
	<!-- ----------------------------------------------------------------------------------------------- -->
	<body onload="setSearchDiv();">
	<!-- ----------------------------------------------------------------------------------------------- -->
	<!-- --------------------- paieskos forma ---------------------------------------------------------- -->
	<!-- ----------------------------------------------------------------------------------------------- -->
	<form action="[{ $oViewConf->getBaseDir() }]index.php" method="get" class="search" id="f.search">
		<div>
			[{ $oViewConf->getHiddenSid() }]
			<input type="hidden" name="cl" value="search"/>
			<input type="text" name="searchparam" onkeyup="showResult(this.value)" onfocus="javascript: clearElementValue(this);" value="[{if $searchparamforhtml}][{$searchparamforhtml}][{else}][{oxmultilang ident="CUST_TOP_SEARCH"}][{/if}]" id="searchparam" class="input" />
			<input id="searchparam_submit" name="button" onclick="javascript:document.getElementById('search_drop').style.display=''; setSearchDiv(); return false;" value="[{oxmultilang ident="CUST_TOP_SURASK"}]" class="submit"/>
		</div>
	</form>