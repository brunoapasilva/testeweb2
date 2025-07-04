<?php
$this->load->view('admincp' . DS . 'view.header');
$this->load->view('admincp' . DS . 'view.sidebar');
?>
<div id="content" class="span10">
    <div>
        <ul class="breadcrumb">
            <li><a href="<?php echo $this->config->base_url; ?>admincp">Home</a> <span class="divider">/</span></li>
            <li><a href="<?php echo $this->config->base_url; ?>admincp/manage-plugins">Manage Plugins</a></li>
        </ul>
    </div>
	<?php $server_list = ($is_multi_server == 0) ? ['all' => ['title' => 'All']] : $this->website->server_list(); ?>
	<div class="row-fluid">
        <div class="span12">
            <ul class="nav nav-pills">
                <li role="presentation" ><a href="<?php echo $this->config->base_url . $this->request->get_controller(); ?>/admin">Server Settings</a></li>
				<li role="presentation" class="dropdown active">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Exchange List<span class="caret"></span></a>
                    <ul class="dropdown-menu">
						<?php foreach($server_list AS $key => $val): ?>
                        <li><a href="<?php echo $this->config->base_url . $this->request->get_controller(); ?>/exchange-list?server=<?php echo $key;?>"><?php echo $val['title'];?></a></li>
						 <?php endforeach;?>
                    </ul>
                </li>
				<li role="presentation" class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Reward List<span class="caret"></span></a>
                    <ul class="dropdown-menu">
						<?php foreach($server_list AS $key => $val): ?>
                        <li><a href="<?php echo $this->config->base_url . $this->request->get_controller(); ?>/reward-list?server=<?php echo $key;?>"><?php echo $val['title'];?></a></li>
						 <?php endforeach;?>
                    </ul>
                </li>
				<li role="presentation" class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Logs<span class="caret"></span></a>
                    <ul class="dropdown-menu">
						<?php foreach($server_list AS $key => $val): ?>
                        <li><a href="<?php echo $this->config->base_url . $this->request->get_controller(); ?>/logs?server=<?php echo $key;?>">Exchange/Reward <?php echo $val['title'];?></a></li>
						<li><a href="<?php echo $this->config->base_url . $this->request->get_controller(); ?>/logs-points?server=<?php echo $key;?>">Points <?php echo $val['title'];?></a></li>
						 <?php endforeach;?>
                    </ul>
                </li>
			</ul>
            <div class="clearfix"></div>
        </div>
    </div>
	<?php
	if(isset($not_found)){
		echo '<div class="alert alert-error">' . $not_found . '</div>';
	}
	else{
		if(isset($error)){
			echo '<div class="alert alert-error">' . $error . '</div>';
		}
		if(isset($success)){
			echo '<div class="alert alert-success">' . $success . '</div>';
		}
	?>
	<div class="row-fluid">
        <div class="box span12">
            <div class="tab-content">          
				<div class="box-header well">
					<h2><i class="icon-edit"></i>Edit Exchange</h2>
				</div>
				<div class="box-content">
						<form class="form-horizontal" method="POST" action="" id="edit_exchange">
							<div class="control-group">
								<label class="control-label" for="currency_amount">Reward Amount </label>
								<div class="controls">
									<input type="text" class="span3 typeahead" id="currency_amount" name="currency_amount" value="<?php if(isset($achData['currency_amount'])){ echo $achData['currency_amount']; } else { echo 0; }  ?>" required />
									<p class="help-block">Custom currency reward amount.</p>
								</div>
							</div>
							<div class="control-group" id="items3">
								<label class="control-label" for="items">Exchange Items</label>
								<div class="controls" id="itemlist">
									<?php 
									if(!empty($achData['items'])){ 
										$i = 0;
										foreach($achData['items'] AS $data){
									?>
									<div id="item_<?php echo $i + 1;?>" <?php if($i > 0){?>style="margin-top:2px;"<?php } ?>>
										Count: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_count[]" value="<?php echo $data['amount'];?>" required /> 
										Category: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_category[]" value="<?php echo $data['cat'];?>" /> 
										Index: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_index[]" value="<?php echo $data['id'];?>" /> 
										Level: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_level[]" value="<?php echo $data['lvl'];?>" placeholder="0-15" /> 
										Skill: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_skill[]" value="<?php echo $data['skill'];?>" placeholder="0/1" /> 
										Luck: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_luck[]" value="<?php echo $data['luck'];?>" placeholder="0/1" /> 
										Option: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_option[]" value="<?php echo $data['opt'];?>" placeholder="0-7" /> 
										Excellent: <input class="form-control" style="width:70px; display: inline;" type="text" name="item_excellent[]" value="<?php echo $data['exe'];?>" placeholder="1,1,1,1,1,1" /> 
										Ancient: <input class="form-control" style="width:70px; display: inline;" type="text" name="item_ancient[]" value="<?php echo $data['anc'];?>" placeholder="0/5/6/9/10" /> 
										<button class="btn btn-danger removeItem" name="removeItem" id="remove_<?php echo $i + 1;?>"> <i class="icon-remove"></i></button>
										<?php if($i == 0){?><button class="btn btn-success" name="addItem" id="addItem"><i class="icon-plus"></i></button><?php } ?>
									</div>
									<?php
											$i++;
										}
									} else { 
									?>
									<div id="item_1">
										Count: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_count[]" value="0" required /> 
										Category: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_category[]" value="" /> 
										Index: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_index[]" value="" /> 
										Level: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_level[]" value="" placeholder="0-15" /> 
										Skill: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_skill[]" value="" placeholder="0/1" /> 
										Luck: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_luck[]" value="" placeholder="0/1" /> 
										Option: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_option[]" value="" placeholder="0-7" /> 
										Excellent: <input class="form-control" style="width:70px; display: inline;" type="text" name="item_excellent[]" value="" placeholder="1,1,1,1,1,1" /> 
										Ancient: <input class="form-control" style="width:70px; display: inline;" type="text" name="item_ancient[]" value="" placeholder="0/5/6/9/10" /> 
										<button class="btn btn-danger removeItem" name="removeItem" id="remove_1"> <i class="icon-remove"></i></button>
										<button class="btn btn-success" name="addItem" id="addItem"><i class="icon-plus"></i></button>
									</div>
									<?php } ?>	
								</div>
							</div>
							<div class="form-actions">
								<button type="submit" class="btn btn-primary" name="edit_exchange" id="edit_exchange">Submit</button>
							</div>
						</form>
						<script>
						$(document).ready(function() {
							$('#addItem').on("click", function(e) {
								e.preventDefault();
								var divId = parseInt($('#itemlist').children().last().attr('id').split('_')[1]) + 1;

								var html = '<div id="item_'+divId+'" style="margin-top:2px;">';
								html += 'Count: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_count[]" value="0" required />';
								html += ' Category: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_category[]" value="0" required />';
								html += ' Index: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_index[]" value="0" required />';
								html += ' Level: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_level[]" value="" placeholder="0-15" />';
								html += ' Skill: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_skill[]" value="" placeholder="0/1" />';
								html += ' Luck: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_luck[]" value="" placeholder="0/1" />';
								html += ' Option: <input class="form-control" style="width:40px; display: inline;" type="text" name="item_option[]" value="" placeholder="0-7" />';
								html += ' Excellent: <input class="form-control" style="width:70px; display: inline;" type="text" name="item_excellent[]" value="" placeholder="1,1,1,1,1,1" />';
								html += ' Ancient: <input class="form-control" style="width:70px; display: inline;" type="text" name="item_ancient[]" value="" placeholder="0/5/6/9/10" />';
								html += ' <button class="btn btn-danger removeItem"name="removeItem" id="remove_'+divId+'"> <i class="icon-remove"></i></button>';
								html += '</div>';
								$('#itemlist').append(html);
							});
							$(document).on("click", ".removeItem", function(e) {
								e.preventDefault();
								var id = $(this).attr('id').split('_')[1];
								if(id == 1)
									return false;
								$('#item_'+id).empty();
								$('#item_'+id).hide();
							});
						});
						</script>
				</div>
            </div>
        </div>
    </div>
	<div class="row-fluid">
        <div class="box span12">
            <div class="tab-content">          
				<div class="box-header well">
					<h2><i class="icon-edit"></i><?php echo $this->website->get_title_from_server($server); ?> Exchange List</h2>
				</div>
				<div class="box-content">
					<table class="table">
					<thead>
						<tr>
							<th>Exchange id</th>
							<th>Reward Amount</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
					<?php
						if(!empty($exchange_list[$server])){
							ksort($exchange_list[$server]);
							foreach($exchange_list[$server] AS $id => $settings){
								?>
								<tr>          
									<td><?php echo $id;?></td>
									<td><?php echo $settings['currency_amount'];?></td>
									<td>
										<a class="btn btn-info" href="<?php echo $this->config->base_url . str_replace('_', '-', $this->request->get_controller()) .'/edit-exchange/' . $id;?>/<?php echo $server;?>">
											<i class="icon-edit icon-white"></i>  
											Edit                                            
										</a>
										<a class="btn btn-danger" onclick="ask_url('Are you sure to delete exchange?', '<?php echo $this->config->base_url . str_replace('_', '-', $this->request->get_controller()) .'/delete-exchange/' . $id;?>/<?php echo $server;?>')" href="#">
											<i class="icon-trash icon-white"></i> 
											Delete
										</a>
										<?php if($settings['status'] == 1){ ?>
										<a class="btn btn-danger" href="<?php echo $this->config->base_url . str_replace('_', '-', $this->request->get_controller()) .'/change-exchange-status/' . $id .'-'.(0);?>/<?php echo $server;?>">
											<i class="icon-edit icon-white"></i> 
											Disable
										</a>
										<?php } else { ?>
										<a class="btn btn-success" href="<?php echo $this->config->base_url . str_replace('_', '-', $this->request->get_controller()) .'/change-exchange-status/' . $id .'-'.(1);?>/<?php echo $server;?>">
											<i class="icon-edit icon-white"></i> 
											Enable
										</a>
										<?php } ?>
									</td>
								</tr>        
								<?php
							}
						} else{
							echo '<tr><td colspan="6"><div class="alert alert-info">No exchange found.</div></td></tr>';
						}
					?>
					</tbody>
					</table>
				</div>
            </div>
        </div>
    </div>
	<?php	
	} 
	?>
</div>
<?php
$this->load->view('admincp' . DS . 'view.footer');
?>
