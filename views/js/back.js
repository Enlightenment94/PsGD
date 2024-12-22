/**
* 2007-2024 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2024 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

function strGetImd(){
	console.log("Hello");
	document.getElementById('alertStrImgProcess').style.display = 'block';
	var xhr = new XMLHttpRequest();
	
	xhr.onreadystatechange = function() {
		if (this.readyState === 4 && this.status === 200) {
			if(this.responseText != ""){
				//alert(this.responseText);
				
				document.getElementById('alertStrImgResponse').innerHTML = this.responseText;
				document.getElementById('alertStrImg').style.display = 'block';
				document.getElementById('alertStrImgProcess').style.display = 'none';
			}
		} else {
			if(this.responseText != ""){
				//alert(this.responseText);
			}
		}
	};

	console.log(controllerSyncLink)

	xhr.open('GET', controllerSyncLink + '&onlyContent=t', true);
	xhr.send();
}

function strRemoveImgDialog(){
	document.getElementById('alertStrImgRm').style.display = 'block';
}

function strRemoveImg(){
	console.log("Str remove Img");
	var xhr = new XMLHttpRequest();
	
	xhr.onreadystatechange = function() {
		if (this.readyState === 4 && this.status === 200) {
			if(this.responseText != ""){
				//alert(this.responseText);
				document.getElementById('alertStrImgRm').style.display = 'none';
				location.reload();
			}
		} else {
			if(this.responseText != ""){
				//alert(this.responseText);
			}
		}
	};

	console.log(controllerFlushImg)
	xhr.open('GET', controllerFlushImg, true);
	xhr.send();
}

function strImgBtnClose(){
	document.getElementById('alertStrImg').style.display = 'none';
	document.getElementById('alertStrImgRm').style.display = 'none';
}

function strImgBtnRefresh(){
	location.reload();
}

function strGetVideo(){
	console.log("Hello");
	document.getElementById('alertStrImgProcessVideo').style.display = 'block';
	var xhr = new XMLHttpRequest();
	
	xhr.onreadystatechange = function() {
		if (this.readyState === 4 && this.status === 200) {
			if(this.responseText != ""){
				//alert(this.responseText);

				var tempDiv = document.createElement('div');
				tempDiv.innerHTML = this.responseText;
				var onlyContent = tempDiv.querySelector('#onlycontent').innerHTML;
				console.log(onlyContent)

				document.getElementById('alertStrImgResponse').innerHTML = onlyContent;
				document.getElementById('alertStrImg').style.display = 'block';
				document.getElementById('alertStrImgProcessVideo').style.display = 'none';
			}
		} else {
			if(this.responseText != ""){
				//alert(this.responseText);
			}
		}
	};
	xhr.open('GET', StrDownloadVideoController + '&onlyContent=t', true);
	xhr.send();
}