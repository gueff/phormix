<?php

function debug($mData){echo '<pre>' . print_r($mData, TRUE) . '</pre><hr />';}


require_once '../Phormix.php';
require_once '../PhormixValidate.php';
require_once '../PhormixSanitize.php';


// Phormix
$oPhormix = new \Phormix\Model\Phormix();
$oPhormix->init('config/comment.json');
$oPhormix->run();

$aConfig = $oPhormix->getConfigArray();
$aFormData = $oPhormix->getFormDataArray();
$aError = $oPhormix->getErrorArray();




?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta content="IE=edge" http-equiv="X-UA-Compatible">
		<meta content="width=device-width, initial-scale=1" name="viewport">
		<title>Phormix</title>
		<style>
			.block {
				width: 48%;
			}
			button {
				width: 100%;
			}
		</style>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css" />
	</head>
	<body>
		<div class="container">
			<div class="header clearfix">
				<h3 class="text-muted">Phormix</h3>
			</div>

			<?php
            /**
             * show errors
             */
			if (!empty($aError))
			{
				foreach ($aError as $sError)
				{
				?>
				<ul>
					<li class="text-danger"><?php echo $sError; ?></li>
				</ul>
				<?php
				}
			}
			?>
			
			<ul>
				<li>Status: <b><?php echo (int) $oPhormix->getStatus (); ?> </b> <i class="text-muted">(0 = Fail / 1 = Success)</i></li>
			</ul>
			
			<form name="<?php echo $aConfig['name'] ?>" 
				  method="<?php echo $aConfig['method'] ?>" 
				  enctype="<?php echo $aConfig['enctype'] ?>"  
				  action="<?php echo $aConfig['action'] ?>" 
				  >
				
				<!-- ticket -->
				<input type="hidden" name="<?php echo $oPhormix->sTicket ?>" value="<?php echo $oPhormix->sTicket ?>" />
				
				<div class="pull-left block">
					<?php
                    
					$i = 0;
                    
					foreach ($aConfig['element'] as $iKey => $aElement)
					{
						$i++;
						if ($i === 8)
						{
						?>
						</div>
						<div class="pull-right block">				
						<?php
						}
					?>
					<div class="form-group">
						<label for="<?php echo $aElement['attribute']['name']; ?>">
							<?php 
							echo $aElement['label']; 
							if (TRUE === $aElement['attribute']['required'])
							{
							?>
								<span class="text-danger">*</span>
							<?php
							}
							
							if (isset($aElement['filter']['validate']['expect']['value']))
							{
								echo '[ ';
								foreach ($aElement['filter']['validate']['expect']['value'] as $sExpect)
								{
									echo $sExpect . ', ';
								}
								echo ' ]';
							}
							?>
						</label> 
						<input id="<?php echo $aElement['attribute']['name']; ?>" 
							   class="form-control" 
							   type="text" 
							   <?php if (TRUE === $aElement['attribute']['required']){echo 'required="true" ';} ?> 
							   name="<?php echo $aElement['attribute']['name']; ?>" 
							   placeholder="<?php echo $aElement['attribute']['name']; ?>" 
							   value="<?php echo ((isset($_POST[$aElement['attribute']['name']])) ? $_POST[$aElement['attribute']['name']] : $aElement['label']); ?>" 
							   maxlength="<?php echo ((isset($aElement['filter']['sanitize']['maxlength'])) ? $aElement['filter']['sanitize']['maxlength'] : 125); ?>"  
							   >
					</div>				
					<?php
					}
					?>
				</div>
                
				<br clear="all" />				
				<button class="btn btn-primary" type="submit">Submit</button>
			</form>
            <hr>
            <?php debug($oPhormix->getLog()); ?>	
            
		</div>
        
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>		
	</body>
</html>