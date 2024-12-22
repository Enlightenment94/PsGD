<!--<h1>GDriver {$idProduct}</h1>-->
<script>
    var controllerSyncLink = '{$controllerSyncLink}';
    var controllerTestLink = '{$controllerTestLink}';
    var PsDownloadVideoController = '{$PsDownloadVideoController}';
    var controllerFlushImg = '{$controllerFlushImg}';
</script>

<h1>Media z Google Drive</h1>
<pre style='font-size: 18px;'>
<a style='color: #25b9d7;' onclick='strGetImd()' href='#'>Pobierz obrazki</a>
<a style='color: #25b9d7;' onclick='strGetVideo()' href='#'>Pobierz video</a>
<a style='color: #25b9d7;' onclick='strRemoveImgDialog()' href='#'>Usuń obrazki</a>
</pre>

<div id='alertStrImg' class='alter'>
	<div id='alertStrImgResponse'></div>
	<div style='display: flex;'>
			<div style='text-aling: center;' id='strImgBtnClose' onclick='strImgBtnClose()' class='strImgBtn'>Zamknij</div>
			<div style='text-aling: center;' id='strImgBtnRefresh' onclick='strImgBtnRefresh()' class='strImgBtn'>Odśwież</div>
	</div>
</div>
             
<div id='alertStrImgProcess' class='alertStrImgProcessClass'><p>Pobranie obrazków ...</p></div>
<div id='alertStrImgProcessVideo' class='alertStrImgProcessClass'><p>Pobieranie video ...</p></div>

<div id='alertStrImgRm' class='alter'>
	<div style='display: flex;'>
			<div style='text-aling: center;' id='strImgRmBtnYes' onclick='strRemoveImg()' class='strImgBtn'>Yes</div>
			<div style='text-aling: center;' id='strImgRmBtnClose' onclick='strImgBtnClose()' class='strImgBtn'>No</div>
	</div>
</div>