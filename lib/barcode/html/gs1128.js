var nbIdentifiers=0;var idRow=0;function addIdentifier(b){nbIdentifiers++;idRow++;if(document.getElementById("identifiersContainer").style.display==="none"){document.getElementById("identifiersContainer").style.display=""}if(document.getElementById("identifiersButton").style.display==="none"){document.getElementById("identifiersButton").style.display=""}var a=document.createElement("DIV");a.id="identifier_"+idRow;a.style.height="25px";a.style.position="relative";a.style.marginTop="2px";a.innerHTML='<input type="text" name="textTemp[0][]" value="'+b+'" style="width:40px;" /> - <input type="text" name="textTemp[1][]" style="width:295px;" /><a href="javascript:removeIdentifier('+idRow+');"><img src="delete.png" alt="Delete" style="border:0px; margin-left:5px; margin-top:5px;" /></a>';document.getElementById("identifiersContainer").appendChild(a);document.getElementById("identifier").value=""}function removeIdentifier(a){nbIdentifiers--;document.getElementById("identifiersContainer").removeChild(document.getElementById("identifier_"+a));if(nbIdentifiers===0){document.getElementById("identifiersButton").style.display="none";document.getElementById("identifiersContainer").style.display="none"}}function sendForm(){var e=document.getElementsByName("text2display");var a=document.getElementsByName("barcode_drawer");var c=document.getElementsByName("textTemp[0][]");var d=document.getElementsByName("textTemp[1][]");e[0].value="";for(var b=0;b<c.length;b++){e[0].value=e[0].value+"("+c[b].value+")";e[0].value=e[0].value+d[b].value+unescape("%1D")}a[0].submit()};