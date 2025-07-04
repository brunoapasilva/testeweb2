<div id="content" class="span10">
    <div>
        <ul class="breadcrumb">
            <li><a href="<?php echo $this->config->base_url . ACPURL; ?>">Home</a> <span class="divider">/</span></li>
            <li><a href="<?php echo $this->config->base_url . ACPURL; ?>/gm-manager">GM Manager</a></li>
        </ul>
    </div>
    <?php
        if(isset($error)){
            echo '<div class="alert alert-error span9">' . $error . '</div>';
        }
        if(isset($success)){
            echo '<div class="alert alert-success span9">' . $success . '</div>';
        }
    ?>
    <div class="row-fluid">
        <div class="box span9">
            <div class="box-header well">
                <h2><i class="icon-edit"></i> GM Manager</h2>
            </div>
            <div class="box-content">
                <form class="form-horizontal" method="POST" action="">
                    <fieldset>
                        <legend>Add GameMaster</legend>
                        <div class="control-group">
                            <label class="control-label" for="name">GM </label>

                            <div class="controls">
                                <input type="text" class="span6 typeahead" id="name" name="name" value=""/>

                                <p class="help-block">Enter gm character name.</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="contact">Contact Info </label>

                            <div class="controls">
                                <input type="text" class="span6 typeahead" id="contact" name="contact" value=""/>

                                <p class="help-block">Enter gm contact info. Fo example skype name</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="server">Server </label>

                            <div class="controls">
                                <select id="server" name="server">
                                    <?php
                                        foreach($this->website->server_list() as $key => $value){
                                            echo '<option value="' . $key . '">' . $value['title'] . "</option>\n";
                                        }
                                    ?>
                                </select>

                                <p class="help-block">Select server.</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="system_type">Gm System Type </label>

                            <div class="controls">
                                <select id="system_type" name="system_type">
                                    <option value="1">Default Server</option>
                                    <option value="2">IGCN Server</option>
                                </select>

                                <p class="help-block">Select Gm System Type.</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="ban_acc">Ban Acc </label>

                            <div class="controls">
                                <label class="checkbox inline">
                                    <input type="checkbox" id="ban_acc" name="ban_acc" value="1" data-no-uniform="true">
                                </label>

                                <p class="help-block">GM is allowed to ban accounts.</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="ban_char">Ban Char </label>

                            <div class="controls">
                                <label class="checkbox inline">
                                    <input type="checkbox" id="ban_char" name="ban_char" value="1"
                                           data-no-uniform="true">
                                </label>

                                <p class="help-block">GM is allowed to ban characters.</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="search_acc">Search Acc </label>

                            <div class="controls">
                                <label class="checkbox inline">
                                    <input type="checkbox" id="search_acc" name="search_acc" value="1"
                                           data-no-uniform="true">
                                </label>

                                <p class="help-block">GM is allowed to search account by character name.</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="acc_details">Acc Details </label>

                            <div class="controls">
                                <label class="checkbox inline">
                                    <input type="checkbox" id="acc_details" name="acc_details" value="1"
                                           data-no-uniform="true">
                                </label>

                                <p class="help-block">GM is allowed to view account detailed info.</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="name">Credits Limit </label>

                            <div class="controls">
                                <input type="text" class="span6 typeahead" id="credits_limit" name="credits_limit"
                                       value="0" placeholder="0"/>

                                <p class="help-block">Amount of credits gm can reward to others per day.</p>
                            </div>
                        </div>

                        <div id="gm_command_list_igcn" style="display:none;">
                            <div class="control-group">
                                <label class="control-label" for="event_management">Event Management </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="event_management" name="event_management" value="1"
                                               data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to manage events in server</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="fireworks">FireWorks Command </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="fireworks" name="fireworks" value="2"
                                               data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use firewoks commands</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="guild_commands">Guild Command </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="guild_commands" name="guild_commands" value="8"
                                               data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use guild commands</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="battle_soccer_commands">BattleSoccer Command </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="battle_soccer_commands" name="battle_soccer_commands"
                                               value="16" data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use battle soccer commands</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="chat_dc_move">/ChatBan /Disconnect /Move </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="chat_dc_move" name="chat_dc_move" value="4"
                                               data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use /ChatBan /Disconnect /Mov commands</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="item">/Item </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="item" name="item" value="32" data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use /Item command</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="hide">/Hide </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="hide" name="hide" value="128" data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use /Hide command</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="ban">/Ban </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="ban" name="ban" value="512" data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use /Ban command</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="pk_set">PK Set Command </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="pk_set" name="pk_set" value="64"
                                               data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use PK Set Command</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="skin">/Skin </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="skin" name="skin" value="256" data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to use /Skin Command</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="gm_shop">Gm Shop Access </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="gm_shop" name="gm_shop" value="1024"
                                               data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM is allowed to access Gm Shop</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="invisible_to_monsters">Invisible To Monsters </label>

                                <div class="controls">
                                    <label class="checkbox inline">
                                        <input type="checkbox" id="invisible_to_monsters" name="invisible_to_monsters"
                                               value="2048" data-no-uniform="true">
                                    </label>

                                    <p class="help-block">GM will be hidden from monsters</p>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="valid_until">Valid Until</label>

                                <div class="controls">
                                    <input type="text" class="input-xlarge datepicker" id="valid_until"
                                           name="valid_until" value="<?php echo date(DATE_FORMAT, strtotime('+1 day')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" name="add_gm">Add GM</button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <div class="box span3">
            <div class="box-header well" data-original-title>
                <h2><i class="icon-edit"></i> GM Settings</h2>
            </div>
            <div class="box-content">
                <?php
                    $this->load->view('admincp' . DS . 'view.panel_gm_settings');
                ?>
            </div>
        </div>
    </div>
    <div class="row-fluid">
        <div class="box span12">
            <div class="box-header well" data-original-title>
                <h2>GM List</h2>

            </div>
            <div class="box-content">
                <?php
                    if(isset($no_gms)){
                        echo '<div class="alert alert-info">' . $no_gms . '</div>';
                    } else{
                        echo '<table class="table">
						  <thead>
							  <tr>
								  <th>Account</th>
								  <th>Character</th>
								  <th>Server</th>
								  <th>Action</th>                                        
							  </tr>
						  </thead>   
						  <tbody>';
                        foreach($gm_list as $key => $value){
                            echo '<tr>
								<td>' . htmlspecialchars($value['account']) . '</td>
								<td class="center">' . htmlspecialchars($value['character']) . '</td>
								<td class="center">' . $this->website->get_title_from_server(htmlspecialchars($value['server'])) . '</td>
								<td class="center">
									<a class="btn btn-info" href="' . $this->config->base_url . ACPURL . '/edit-gm/' . $value['character'] . '/' . $value['server'] . '">
										<i class="icon-edit icon-white"></i>  
										Edit                                            
									</a>
									<a class="btn btn-danger" href="' . $this->config->base_url . ACPURL . '/delete-gm/' . $value['character'] . '/' . $value['server'] . '">
										<i class="icon-trash icon-white"></i> 
										Delete
									</a>
								</td>  
							  </tr>';
                        }
                        echo '</tbody></table>';
                    }
                ?>
            </div>
        </div>
    </div>
</div>