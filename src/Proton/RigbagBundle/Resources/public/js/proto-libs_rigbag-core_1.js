var coreLoadingData=false;var coreRefreshInterval=null;function init(){rbApp.user.refreshAccount();startRefresh()}function startRefresh(){coreRefreshInterval=setInterval("rbApp.user.refreshAccount()",30000)}function stopRefresh(){clearInterval(coreRefreshInterval)}function setUrl(a){var b=false;if(a==rbApp.getHash()){b=true}window.location.hash=a;if(b){rbApp.getHash();rbApp.parseHash()}}function userPanelMessageClick(a){$("#user-panel .popover").fadeOut(function(){$(this).removeClass("in")})}function observerSearch(a){}function sendMail(b,c){var a={type:b,data:c};$.ajax({type:"POST",url:APP_PATH+"mail/send/",data:a,cache:false,dataType:"json",success:function(d){}})}function parseGMapPlace(e){var f={formated:null,countryCode:null,lat:null,lng:null};for(var d in e.address_components){for(var c in e.address_components[d].types){if(e.address_components[d].types[c]=="country"){f.countryCode=e.address_components[d].short_name}}}f.formated=e.formatted_address;f.lat=e.geometry.location.Ya;f.lng=e.geometry.location.Za;return f}function selectMenuElement(b){var a=$(b).parent().parent();$(a).children("li").removeClass("active");$(b).parent().addClass("active")}function selectMenuElement2(b){var a=$(b).parent();$(a).children("li").removeClass("active");$(b).addClass("active")}function initDropdown(d,b){var c=$(d).children(".cont").children("ul");var a=$(c).children("li");var e=$(d).children(".cont").children(".value");$(a).click(function(){$(a).removeClass("current");$(this).addClass("current");$(c).slideUp("fast");$(b).val($(this).attr("data-id"));$(e).html($(this).html())});$(d).hover(function(){$(c).slideDown("fast")},function(){$(c).slideUp("fast")})}function initDropdown2(sel,valSel,callback){var ul=$(sel).children(".cont").children("ul");var li=$(ul).children("li");var val=$(sel).children(".cont").children(".value");$(li).click(function(){$(li).removeClass("current");$(this).addClass("current");$(ul).slideUp("fast");$(valSel).val($(this).attr("data-id"));$(val).html($(this).html());if(callback!=undefined){eval(callback)}});$(sel).click(function(){if($(this).children(".cont").hasClass("opened")){$(ul).slideUp("fast");$(this).children(".cont").removeClass("opened")}else{$(ul).slideDown("fast");$(this).children(".cont").addClass("opened")}})}function showLoader(){$("#main-loader").show()}function hideLoader(){$("#main-loader").hide()};