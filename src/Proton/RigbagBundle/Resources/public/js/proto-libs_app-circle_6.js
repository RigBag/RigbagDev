RigBagCircle=function(){};RigBagCircle.prototype.remove=function(a){if(confirm("Are you sure?")){$("#circle-"+a).fadeOut();$.ajax({type:"POST",url:APP_PATH+"circles/delete/"+a+"/",data:{},cache:false,dataType:"json",success:function(b){}})}};RigBagCircle.prototype.searchJoin=function(){rbApp.callAction("circles/join/search/?q="+$("#key").val())};RigBagCircle.prototype.join=function(a){var b=$(a).attr("data-id");$.ajax({type:"POST",url:APP_PATH+"circles/add/"+b+"/",data:{},cache:false,dataType:"json",success:function(c){$(a).fadeOut()}})};