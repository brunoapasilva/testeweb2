<?php
    in_file();

    class account_panel extends controller
    {
        protected $vars = [], $errors = [];

        public function __construct()
        {
            parent::__construct();
            $this->load->helper('website');
            $this->load->lib('session', ['DmNCMS']);
			$this->session->checkSession();
			$this->load->lib('csrf');						 
            $this->load->model('character');
            $this->load->helper('breadcrumbs', [$this->request]);
            $this->load->helper('meta');
            if($this->session->userdata(['user' => 'server'])){
                $this->load->lib(['game_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']))]);
            }
        }

        public function index()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
				$this->vars['char_list'] = $this->Mcharacter->load_char_list();
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.index', $this->vars);
            } else{
                $this->login();
            }
        }

        public function level_reward()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                $this->vars['level_rewards'] = $this->config->values('level_rewards_config');
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.level_reward', $this->vars);
            } else{
                $this->login();
            }
        }

        public function reset()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                $reset_config = $this->config->values('reset_config', $this->session->userdata(['user' => 'server']));
				if(defined('CUSTOM_RESET_REQ_ITEMS') && CUSTOM_RESET_REQ_ITEMS == true){
					$reset_items_config = $this->config->values('reset_items_config', $this->session->userdata(['user' => 'server']));
				}
                if(!$reset_config){
                    $this->vars['error'] = __('Reset configuration for this server not found.');
                } else{
                    if($reset_config['allow_reset'] == 0){
                        $this->vars['error'] = __('Reset function is disabled for this server');
                    } else{
                        unset($reset_config['allow_reset']);
                        $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                        $this->vars['chars'] = [];
                        $this->vars['res_info'] = [];
                        if($this->vars['char_list'] != false){
                            foreach($this->vars['char_list'] AS $char){
                                foreach($reset_config AS $key => $values){
                                    list($start_res, $end_res) = explode('-', $key);
                                    if($char['resets'] >= $start_res && $char['resets'] < $end_res){
										if(defined('CUSTOM_RESET_NORIA') && CUSTOM_RESET_NORIA == true){
											$addonLVL = ($char['resets'] > 0) ? $char['resets'] * RESET_NORIA_LVL_INCREASE : 0;
											$values['level'] = $values['level'] + $addonLVL;
											if($values['level'] > 400){
												$values['level'] = 400;
											}
										}
                                        $this->vars['res_info'][$char['name']] = $values;
										
										if(defined('CUSTOM_RESET_REQ_ITEMS') && CUSTOM_RESET_REQ_ITEMS == true){
											$this->load->lib('iteminfo');
											$this->load->lib('createitem', [MU_VERSION, SOCKET_LIBRARY, $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size')]);
											if(!empty($reset_items_config[$start_res.'-'.$end_res])){
												$this->vars['res_info'][$char['name']]['reqItems'] = [];
												foreach($reset_items_config[$start_res.'-'.$end_res] AS $cat => $items){												
													if(count($items) > 0){
														$check = $this->website->checkResetItem($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), bin2hex($char['name']), $start_res.'-'.$end_res, $cat);													
														if($check == false){
															$randItem = array_rand($items, 1);
															if($this->iteminfo->setItemData($items[$randItem]['id'], $items[$randItem]['cat'], $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size'))){
																$this->createitem->setItemData($this->iteminfo->item_data);
																$this->createitem->id($items[$randItem]['id']);
																$this->createitem->cat($items[$randItem]['cat']);
																$this->createitem->refinery(false);
																$this->createitem->serial(0);
																if($this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size') == 64){
																	$this->createitem->serial2(true);
																}
																if($items[$randItem]['minLvl'] == $items[$randItem]['maxLvl']){
																	$this->createitem->lvl($items[$randItem]['minLvl']);
																}
																else{
																	$this->createitem->lvl(rand($items[$randItem]['minLvl'], $items[$randItem]['maxLvl']));
																	
																}
																$this->createitem->skill(false);
																$this->createitem->luck(false);
																if($items[$randItem]['minOpt'] == $items[$randItem]['maxOpt']){
																	$this->createitem->opt($items[$randItem]['minOpt']);
																}
																else{
																	$this->createitem->opt(rand($items[$randItem]['minOpt'], $items[$randItem]['maxOpt']));
																	
																}
																$exeData = explode('|', $items[$randItem]['exe']);
																$iexe = explode(',', $exeData[0]);
																foreach($iexe AS $k => $val){
																	if($val == 0){
																		unset($iexe[$k]);
																	}
																}
																$totaExe = count($iexe);
																$randomizer = explode('-', $exeData[1]);
																$exe_opts = [0 => 1, 1 => 2, 2 => 4, 3 => 8, 4 => 16, 5 => 32];
																
																if($randomizer[1] > 0){
																	if($randomizer[1] > $totaExe)
																		$randomizer[1] = $totaExe;
																	$rand = rand($randomizer[0], $randomizer[1]);
																	if($rand == 0){
																		$iexe = [];
																	}
																	else{
																		$iexe = array_rand($iexe, $rand);
																		if(!is_array($iexe))
																			$iexe = [$iexe => 1];
																		else{
																			$newArr = [];
																			foreach($iexe AS $k => $val){
																				$newArr[$val] = 1;
																			}
																			$iexe = $newArr;
																		}
																	}
																}
																
																$exe = 0;
																if(!empty($iexe)){		
																	foreach($iexe as $key => $exe_opt){
																		if($exe_opt == 1){
																			$exe += $exe_opts[$key];
																		}
																	}
																}
																$this->createitem->addStaticExe($exe);

																$items[$randItem]['hex'] = $this->createitem->to_hex();
																$this->website->addResetReqItems($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), bin2hex($char['name']), $start_res.'-'.$end_res, $cat, $randItem, $items[$randItem]);
																$this->vars['res_info'][$char['name']]['reqItems'][$cat][$randItem] = $items[$randItem];
															}
														}
														else{
															$items[$check['config_id']]['hex'] = $check['hex'];
															$this->vars['res_info'][$char['name']]['reqItems'][$cat][$check['config_id']] = $items[$check['config_id']];
														}
													}
												}
												$this->vars['res_info'][$char['name']]['range'] = $start_res.'-'.$end_res;
											}
										}
                                        break;
                                    }
                                }
                                $this->vars['chars'][$char['name']] = [
									'level' => $char['level'], 
									'Class' => $char['Class'], 
									'resets' => $char['resets'], 
									'gresets' => $char['gresets'], 
									'money' => $char['money'], 
									'res_info' => isset($this->vars['res_info'][$char['name']]) ? $this->vars['res_info'][$char['name']] : false
								];
                            }
                        } else{
                            $this->vars['error'] = __('Character not found.');
                        }
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.reset_character', $this->vars);
            } else{
                $this->login();
            }
        }
		
		public function skip_reset_item(){
			if($this->session->userdata(['user' => 'logged_in'])){
				$id = $_POST['id'];
				$char = $_POST['Char'];
				$range = $_POST['range'];
				$cat = $_POST['cat'];
				$status = $this->website->checkCompletedResetItem($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $char, $id, $range, $cat);

				if($status != false){
					if($status['is_skipped'] == 1)
						json(['error' => __('Item requirement already skipped.')]);
					else{
						if($status['is_completed'] == 1)
							json(['error' => __('Item requirement already completed.')]);
						else{
							if($status['skip_price_type'] == 0)
								json(['error' => __('This item cannot be skipped.')]);
							else{
								 $statusCr = $this->website->get_user_credits_balance($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $status['skip_price_type'], $this->session->userdata(['user' => 'id']));
								 if($statusCr['credits'] < $status['skip_price']){
									json(['error' => sprintf(__('You have insufficient amount of %s'), $this->website->translate_credits($status['skip_price_type'], $this->session->userdata(['user' => 'server'])))]);
								 }
								 else{
									 $this->website->charge_credits($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $status['skip_price'], $status['skip_price_type']);
									 $this->website->setSkippedResetItem($id, $char, $this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $range, $cat);
									 $this->Mcharacter->add_account_log('Skipped reset req item ' . $this->website->hex2bin($char) . ' for ' . $this->website->translate_credits($status['skip_price_type'], $this->session->userdata(['user' => 'server'])) . '', -$status['skip_price'], $this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']));
                                     json(['success' => __('Item skipped.')]);   
								 }
							}								
						}							
					}
					
				}
				else{
					json(['error' => __('Item not found.')]);
				}
			}
			else{
				json(['error' => __('Please login into website.')]);
			}  
		}
		
		public function check_reset_item(){
			if($this->session->userdata(['user' => 'logged_in'])){
				$id = $_POST['id'];
				$char = $_POST['Char'];
				$range = $_POST['range'];
				$cat = $_POST['cat'];
				$status = $this->website->checkCompletedResetItem($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $char, $id, $range, $cat);

				if($status != false){
					if($status['is_skipped'] == 1)
						json(['error' => __('Item requirement already skipped.')]);
					else{
						if($status['is_completed'] == 1)
							json(['error' => __('Item requirement already completed.')]);
						else{
							$itemC = $status['hex'];
							if(isset($itemC)){
								 $this->load->lib('iteminfo');
								 $this->iteminfo->itemData($itemC);
								 $idd = $this->iteminfo->id;
								 $type = $this->iteminfo->type;
								 $lvl = (int)substr($this->iteminfo->getLevel(), 1);
								 $opt = ($this->iteminfo->getOption()*4);
								 $exe = $this->iteminfo->exeForCompare();
								 $this->load->model('warehouse');
								 $items = $this->Mwarehouse->list_web_items();
								 if(empty($items))
									json(['error' => __('Item not found in web warehouse.')]);
								 else{
									 $this->load->lib('iteminfo');
									 $found = false;
									 $lvlOk = false;
									 $optOk = false;
									 $exe0Ok = false;
									 $exe1Ok = false;
									 $exe2Ok = false;
									 $exe3Ok = false;
									 $exe4Ok = false;
									 $exe5Ok = false; 
									 foreach($items AS $item){
										 $itemInfo = $this->Mwarehouse->load_item_info($item['item']);
										 if($itemInfo['info']['id'] == $idd && $itemInfo['info']['cat'] == $type){
											
											if($itemInfo['info']['lvl'] >= $lvl){
												$lvlOk = true;
											}
											if($itemInfo['info']['opt'] >= $opt){
												$optOk = true;
											}
											
											if($itemInfo['exe_opts'][0] == 1){
												if($exe[0] <= $itemInfo['exe_opts'][0]){
													$exe0Ok = true;
												}
											}
											else{
												$exe0Ok = true;
											}
											if($exe[1] == 1){
												if($exe[1] <= $itemInfo['exe_opts'][1]){
													$exe1Ok = true;
												}
											}
											else{
												$exe1Ok = true;
											}
											if($exe[2] == 1){
												if($exe[2] <= $itemInfo['exe_opts'][2]){
													$exe2Ok = true;
												}
											}
											else{
												$exe2Ok = true;
											}
											if($exe[3] == 1){
												if($exe[3] <= $itemInfo['exe_opts'][3]){
													$exe3Ok = true;
												}
											}
											else{
												$exe3Ok = true;
											}
											
											if($exe[4] == 1){
												if($exe[4] <= $itemInfo['exe_opts'][4]){
													$exe4Ok = true;
												}
											}
											else{
												$exe4Ok = true;
											}

											if($exe[5] == 1){
												if($exe[5] <= $itemInfo['exe_opts'][5]){
													$exe5Ok = true;
												}
											}
											else{
												$exe5Ok = true;
											}

											if($lvlOk == true && $optOk == true && $exe0Ok == true && $exe1Ok == true && $exe2Ok == true && $exe3Ok == true && $exe4Ok == true && $exe5Ok == true){
												$found = $item['id'];
												break;
											}
										 }
									 }
									 if($found == false){
										 json(['error' => __('Item not found in web warehouse.')]);
									 }
									 else{
										 $this->Mwarehouse->remove_web_item($found);
										 
										 $this->website->setCompletedResetItem($id, $char, $this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $range, $cat);
										 json(['success' => __('Item found and removed.')]);
									 }
								 }
							}
							else{
								json(['error' => __('Item not found in config.')]);
							}						
						}							
					}
					
				}
				else{
					json(['error' => __('Item not found.')]);
				}
			}
			else{
				json(['error' => __('Please login into website.')]);
			}  
		}

        public function grand_reset()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                $reset_config = $this->config->values('reset_config', $this->session->userdata(['user' => 'server']));
                $greset_config = $this->config->values('greset_config', $this->session->userdata(['user' => 'server']));
				if(defined('CUSTOM_GRESET_REQ_ITEMS') && CUSTOM_GRESET_REQ_ITEMS == true){
					$reset_items_config = $this->config->values('greset_items_config', $this->session->userdata(['user' => 'server']));
				}
                if(!$greset_config){
                    $this->vars['error'] = __('Grand Reset configuration for this server not found.');
                } else{
                    if($greset_config['allow_greset'] == 0){
                        $this->vars['error'] = __('Grand Reset function is disabled for this server');
                    } else{
                        unset($greset_config['allow_greset']);
                        if(isset($reset_config)){
                            unset($reset_config['allow_reset']);
                        }
                        $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                        $this->vars['chars'] = [];
                        $this->vars['gres_info'] = [];
                        if($this->vars['char_list'] != false){
                            foreach($this->vars['char_list'] AS $char){
                                foreach($greset_config AS $key => $values){
                                    list($start_gres, $end_gres) = explode('-', $key);
                                    if($char['gresets'] >= $start_gres && $char['gresets'] < $end_gres){
                                        $this->vars['gres_info'][$char['name']] = $values;
										if(defined('CUSTOM_GRESET_REQ_ITEMS') && CUSTOM_GRESET_REQ_ITEMS == true){
											$this->load->lib('iteminfo');
											$this->load->lib('createitem', [MU_VERSION, SOCKET_LIBRARY, $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size')]);
											if(!empty($reset_items_config[$start_gres.'-'.$end_gres])){
												$this->vars['gres_info'][$char['name']]['reqItems'] = [];
												foreach($reset_items_config[$start_gres.'-'.$end_gres] AS $cat => $items){												
													if(count($items) > 0){
														$check = $this->website->checkGResetItem($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), bin2hex($char['name']), $start_gres.'-'.$end_gres, $cat);													
														if($check == false){
															$randItem = array_rand($items, 1);
															if($this->iteminfo->setItemData($items[$randItem]['id'], $items[$randItem]['cat'], $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size'))){
																$this->createitem->setItemData($this->iteminfo->item_data);
																$this->createitem->id($items[$randItem]['id']);
																$this->createitem->cat($items[$randItem]['cat']);
																$this->createitem->refinery(false);
																$this->createitem->serial(0);
																if($this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size') == 64){
																	$this->createitem->serial2(true);
																}
																if($items[$randItem]['minLvl'] == $items[$randItem]['maxLvl']){
																	$this->createitem->lvl($items[$randItem]['minLvl']);
																}
																else{
																	$this->createitem->lvl(rand($items[$randItem]['minLvl'], $items[$randItem]['maxLvl']));
																	
																}
																$this->createitem->skill(false);
																$this->createitem->luck(false);
																if($items[$randItem]['minOpt'] == $items[$randItem]['maxOpt']){
																	$this->createitem->opt($items[$randItem]['minOpt']);
																}
																else{
																	$this->createitem->opt(rand($items[$randItem]['minOpt'], $items[$randItem]['maxOpt']));
																	
																}
																$exeData = explode('|', $items[$randItem]['exe']);
																$iexe = explode(',', $exeData[0]);
																foreach($iexe AS $k => $val){
																	if($val == 0){
																		unset($iexe[$k]);
																	}
																}
																$totaExe = count($iexe);
																$randomizer = explode('-', $exeData[1]);
																$exe_opts = [0 => 1, 1 => 2, 2 => 4, 3 => 8, 4 => 16, 5 => 32];
																
																if($randomizer[1] > 0){
																	if($randomizer[1] > $totaExe)
																		$randomizer[1] = $totaExe;
																	$rand = rand($randomizer[0], $randomizer[1]);
																	if($rand == 0){
																		$iexe = [];
																	}
																	else{
																		$iexe = array_rand($iexe, $rand);
																		if(!is_array($iexe))
																			$iexe = [$iexe => 1];
																		else{
																			$newArr = [];
																			foreach($iexe AS $k => $val){
																				$newArr[$val] = 1;
																			}
																			$iexe = $newArr;
																		}
																	}
																}
																
																$exe = 0;
																if(!empty($iexe)){		
																	foreach($iexe as $key => $exe_opt){
																		if($exe_opt == 1){
																			$exe += $exe_opts[$key];
																		}
																	}
																}
																$this->createitem->addStaticExe($exe);

																$items[$randItem]['hex'] = $this->createitem->to_hex();
																$this->website->addGResetReqItems($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), bin2hex($char['name']), $start_gres.'-'.$end_gres, $cat, $randItem, $items[$randItem]);
																$this->vars['res_info'][$char['name']]['reqItems'][$cat][$randItem] = $items[$randItem];
															}
														}
														else{
															$items[$check['config_id']]['hex'] = $check['hex'];
															$this->vars['gres_info'][$char['name']]['reqItems'][$cat][$check['config_id']] = $items[$check['config_id']];
														}
													}
												}
												$this->vars['gres_info'][$char['name']]['range'] = $start_gres.'-'.$end_gres;
											}
										}
                                        break;
                                    }
                                }
                                $bonus_reset_stats = 0;
                                if(isset($this->vars['gres_info'][$char['name']])){
                                    if($this->vars['gres_info'][$char['name']]['bonus_reset_stats'] == 1){
                                        $reset_data = [];
                                        foreach($reset_config AS $key => $values){
                                            $reset_range = explode('-', $key);
                                            for($i = $reset_range[0]; $i < $reset_range[1]; $i++){
                                                $reset_data[$i] = $values['bonus_points'];
                                            }
                                        }
                                        foreach($reset_data AS $res => $data){
                                            if($char['resets'] <= $res)
                                                break;
                                            $bonus_reset_stats += $data[$char['Class']];
                                        }
                                    }
                                }
                                $this->vars['chars'][$char['name']] = ['level' => $char['level'], 'Class' => $char['Class'], 'resets' => $char['resets'], 'gresets' => $char['gresets'], 'money' => $char['money'], 'gres_info' => isset($this->vars['gres_info'][$char['name']]) ? $this->vars['gres_info'][$char['name']] : false, 'bonus_reset_stats' => $bonus_reset_stats];
                            }
                        } else{
                            $this->vars['error'] = __('Character not found.');
                        }
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.greset_character', $this->vars);
            } else{
                $this->login();
            }
        }
		
		public function skip_greset_item(){
			if($this->session->userdata(['user' => 'logged_in'])){
				$id = $_POST['id'];
				$char = $_POST['Char'];
				$range = $_POST['range'];
				$cat = $_POST['cat'];
				$status = $this->website->checkCompletedGResetItem($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $char, $id, $range, $cat);

				if($status != false){
					if($status['is_skipped'] == 1)
						json(['error' => __('Item requirement already skipped.')]);
					else{
						if($status['is_completed'] == 1)
							json(['error' => __('Item requirement already completed.')]);
						else{
							if($status['skip_price_type'] == 0)
								json(['error' => __('This item cannot be skipped.')]);
							else{
								 $statusCr = $this->website->get_user_credits_balance($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $status['skip_price_type'], $this->session->userdata(['user' => 'id']));
								 if($statusCr['credits'] < $status['skip_price']){
									json(['error' => sprintf(__('You have insufficient amount of %s'), $this->website->translate_credits($status['skip_price_type'], $this->session->userdata(['user' => 'server'])))]);
								 }
								 else{
									 $this->website->charge_credits($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $status['skip_price'], $status['skip_price_type']);
									 $this->website->setSkippedGResetItem($id, $char, $this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $range, $cat);
									 $this->Mcharacter->add_account_log('Skipped grand reset req item ' . $this->website->hex2bin($char) . ' for ' . $this->website->translate_credits($status['skip_price_type'], $this->session->userdata(['user' => 'server'])) . '', -$status['skip_price'], $this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']));
                                     json(['success' => __('Item skipped.')]);   
								 }
							}								
						}							
					}
					
				}
				else{
					json(['error' => __('Item not found.')]);
				}
			}
			else{
				json(['error' => __('Please login into website.')]);
			}  
		}
		
		public function check_greset_item(){
			if($this->session->userdata(['user' => 'logged_in'])){
				$id = $_POST['id'];
				$char = $_POST['Char'];
				$range = $_POST['range'];
				$cat = $_POST['cat'];
				$status = $this->website->checkCompletedGResetItem($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $char, $id, $range, $cat);

				if($status != false){
					if($status['is_skipped'] == 1)
						json(['error' => __('Item requirement already skipped.')]);
					else{
						if($status['is_completed'] == 1)
							json(['error' => __('Item requirement already completed.')]);
						else{
							$itemC = $status['hex'];
							if(isset($itemC)){
								 $this->load->lib('iteminfo');
								 $this->iteminfo->itemData($itemC);
								 $idd = $this->iteminfo->id;
								 $type = $this->iteminfo->type;
								 $lvl = (int)substr($this->iteminfo->getLevel(), 1);
								 $opt = ($this->iteminfo->getOption()*4);
								 $exe = $this->iteminfo->exeForCompare();
								 $this->load->model('warehouse');
								 $items = $this->Mwarehouse->list_web_items();
								 if(empty($items))
									json(['error' => __('Item not found in web warehouse.')]);
								 else{
									 $this->load->lib('iteminfo');
									 $found = false;
									 $lvlOk = false;
									 $optOk = false;
									 $exe0Ok = false;
									 $exe1Ok = false;
									 $exe2Ok = false;
									 $exe3Ok = false;
									 $exe4Ok = false;
									 $exe5Ok = false; 
									 foreach($items AS $item){
										 $itemInfo = $this->Mwarehouse->load_item_info($item['item']);
										 if($itemInfo['info']['id'] == $idd && $itemInfo['info']['cat'] == $type){
											
											if($itemInfo['info']['lvl'] >= $lvl){
												$lvlOk = true;
											}
											if($itemInfo['info']['opt'] >= $opt){
												$optOk = true;
											}
											
											if($itemInfo['exe_opts'][0] == 1){
												if($exe[0] <= $itemInfo['exe_opts'][0]){
													$exe0Ok = true;
												}
											}
											else{
												$exe0Ok = true;
											}
											if($exe[1] == 1){
												if($exe[1] <= $itemInfo['exe_opts'][1]){
													$exe1Ok = true;
												}
											}
											else{
												$exe1Ok = true;
											}
											if($exe[2] == 1){
												if($exe[2] <= $itemInfo['exe_opts'][2]){
													$exe2Ok = true;
												}
											}
											else{
												$exe2Ok = true;
											}
											if($exe[3] == 1){
												if($exe[3] <= $itemInfo['exe_opts'][3]){
													$exe3Ok = true;
												}
											}
											else{
												$exe3Ok = true;
											}
											
											if($exe[4] == 1){
												if($exe[4] <= $itemInfo['exe_opts'][4]){
													$exe4Ok = true;
												}
											}
											else{
												$exe4Ok = true;
											}

											if($exe[5] == 1){
												if($exe[5] <= $itemInfo['exe_opts'][5]){
													$exe5Ok = true;
												}
											}
											else{
												$exe5Ok = true;
											}

											if($lvlOk == true && $optOk == true && $exe0Ok == true && $exe1Ok == true && $exe2Ok == true && $exe3Ok == true && $exe4Ok == true && $exe5Ok == true){
												$found = $item['id'];
												break;
											}
										 }
									 }
									 if($found == false){
										 json(['error' => __('Item not found in web warehouse.')]);
									 }
									 else{
										 $this->Mwarehouse->remove_web_item($found);
										 
										 $this->website->setCompletedGResetItem($id, $char, $this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $range, $cat);
										 json(['success' => __('Item found and removed.')]);
									 }
								 }
							}
							else{
								json(['error' => __('Item not found in config.')]);
							}						
						}							
					}
					
				}
				else{
					json(['error' => __('Item not found.')]);
				}
			}
			else{
				json(['error' => __('Please login into website.')]);
			}  
		}

        public function add_stats($char = '')
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                if(!$char){
                    $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                    $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.add_stats_info', $this->vars);
                } else{
                    if(!$this->Mcharacter->check_char($this->website->hex2bin($char))){
                        $this->vars['not_found'] = __('Character not found.');
                    }
                    if(count($_POST) > 0){
                        foreach($_POST as $key => $value){
                            $this->Mcharacter->$key = trim($value);
                        }
                        if(!$this->Maccount->check_connect_stat())
                            $this->vars['error'] = __('Please logout from game.'); else{
                            $this->Mcharacter->check_stats();
                            if(!preg_match('/^(\s*|[0-9]+)$/', $this->Mcharacter->vars['str_stat']))
                                $this->vars['error'] = __('Only positive values allowed in') . ' ' . __('Strength') . '.'; else{
                                if(!preg_match('/^(\s*|[0-9]+)$/', $this->Mcharacter->vars['agi_stat']))
                                    $this->vars['error'] = __('Only positive values allowed in') . ' ' . __('Agility') . '.'; else{
                                    if(!preg_match('/^(\s*|[0-9]+)$/', $this->Mcharacter->vars['ene_stat']))
                                        $this->vars['error'] = __('Only positive values allowed in') . ' ' . __('Energy') . '.'; else{
                                        if(!preg_match('/^(\s*|[0-9]+)$/', $this->Mcharacter->vars['vit_stat']))
                                            $this->vars['error'] = __('Only positive values allowed in') . ' ' . __('Vitality') . '.'; else{
                                            if(!preg_match('/^(\s*|[0-9]+)$/', $this->Mcharacter->vars['com_stat']))
                                                $this->vars['error'] = __('Only positive values allowed in') . ' ' . __('Command') . '.'; else{
                                                $this->Mcharacter->set_new_stats();
                                                if(!$this->Mcharacter->check_max_stat_limit())
                                                    $this->vars['error'] = $this->Mcharacter->vars['error']; else{
                                                    if($this->Mcharacter->vars['new_lvlup'] < 0)
                                                        $this->vars['error'] = __('Only positive values allowed in') . ' ' . __('Level Up Points') . '.'; else{
                                                        $this->Mcharacter->add_stats($this->website->hex2bin($char));
                                                        $this->Mcharacter->check_char($this->website->hex2bin($char));
                                                        $this->vars['success'] = __('Stats Have Been Successfully Added.');
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.add_stats', $this->vars);
                }
            } else{
                $this->login();
            }
        }

        public function reset_stats()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.reset_stats', $this->vars);
            } else{
                $this->login();
            }
        }

        public function hide_info()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                $this->vars['hide_time'] = $this->Maccount->check_hide_time();
				if(defined('ELITE_KILLER_HIDE') && ELITE_KILLER_HIDE == true){
					 $this->vars['hide_time_pk'] = $this->Maccount->check_hide_time_pk($this->session->userdata(['user' => 'server']));
				}
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.hide_info', $this->vars);
            } else{
                $this->login();
            }
        }

        public function clear_skilltree()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.clear_skill_tree', $this->vars);
            } else{
                $this->login();
            }
        }

        public function clear_inventory()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.clear_inventory', $this->vars);
            } else{
                $this->login();
            }
        }

        public function buy_zen()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.buy_zen', $this->vars);
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function warp_char()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                $this->vars['char_list'] = $this->Mcharacter->load_char_list();
				$this->vars['teleport_config'] = $this->config->values('teleport_config', $this->session->userdata(['user' => 'server']));

                if(isset($_POST['character'])){
                    foreach($_POST as $key => $value){
                        $this->Mcharacter->$key = trim($value);
                    }
                    if(!$this->Maccount->check_connect_stat())
                        $this->vars['error'] = __('Please logout from game.'); 
					else{
                        if(!$this->Mcharacter->check_char())
                            $this->vars['error'] = __('Character not found.'); 
						else{
							
							if(!empty($this->vars['teleport_config']['teleports'])){
								if(!isset($this->vars['teleport_config']['teleports'][$this->Mcharacter->vars['world']]))
									$this->vars['error'] = __('Invalid location selected.'); 
								else{
									if($this->vars['teleport_config']['teleports'][$this->Mcharacter->vars['world']]['req_lvl'] > $this->Mcharacter->char_info['cLevel']){
										$this->vars['error'] = sprintf(__('Character level too low. Req %d level'), $this->vars['teleport_config']['teleports'][$this->Mcharacter->vars['world']]['req_lvl']); 
									}
									else{
										if($this->vars['teleport_config']['teleports'][$this->Mcharacter->vars['world']]['req_money'] > $this->Mcharacter->char_info['Money']){
											$this->vars['error'] = sprintf(__('Character zen too low. Req %s zen'), $this->website->zen_format($this->vars['teleport_config']['teleports'][$this->Mcharacter->vars['world']]['req_money'])); 
										}
										else{
											$id = $this->Mcharacter->vars['world'];
											$this->Mcharacter->vars['world'] = $this->vars['teleport_config']['teleports'][$id]['map_id'];
											$this->Mcharacter->teleport_char($this->vars['teleport_config']['teleports'][$id]['cords'], $this->vars['teleport_config']['teleports'][$id]['req_money']);
											$this->vars['success'] = __('Character successfully teleported.');
										}
									}
								}
							}
							else{
								$this->vars['error'] = __('teleport_config.json is empty'); 
							}
                        }
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.warp_char', $this->vars);
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function recover_master()
        {
            if(defined('RES_CUSTOM_BACKUP_MASTER') && RES_CUSTOM_BACKUP_MASTER == true){
                if($this->session->userdata(['user' => 'logged_in'])){
                    if($this->website->is_multiple_accounts() == true){
                        $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                    } else{
                        $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                    }
                    $this->load->model('account');
                    $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                    if(isset($_POST['character'])){
                        foreach($_POST as $key => $value){
                            $this->Mcharacter->$key = trim($value);
                        }
                        if(!$this->Maccount->check_connect_stat())
                            $this->vars['error'] = __('Please logout from game.'); 
						else{
                            if(!$this->Mcharacter->check_char('', ', Master'))
                                $this->vars['error'] = __('Character not found.'); 
							else{
                                if(!in_array($this->Mcharacter->char_info['Class'], [2, 3, 7, 18, 19, 23, 34, 35, 39, 49, 50, 54, 65, 66, 70, 82, 83, 87, 97, 98, 102, 114, 118])){
                                    if($this->Mcharacter->char_info['cLevel'] < 400 && $this->Mcharacter->char_info[$this->config->values('table_config', [$this->session->userdata(['user' => 'server']), 'resets', 'column'])] == 0){
                                        $this->vars['error'] = __('Your lvl, or grand resets are too low.');
                                    } else{
                                        if($this->Mcharacter->char_info['Master'] >= 1){
                                            $this->Mcharacter->restore_master_level();
                                            $this->vars['success'] = __('Your master level and master class have been restored.');
                                        } else{
                                            $this->vars['error'] = __('You don\'t have master level points');
                                        }
                                    }
                                } else{
                                    $this->vars['error'] = __('You are not allowed to recover master level.');
                                }
                            }
                        }
                    }
                    $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.recover_char', $this->vars);
                } else{
                    $this->login();
                }
            } else{
                $this->disabled();
            }
        }

        public function pk_clear()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.pk_clear', $this->vars);
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function vote_reward()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                $this->vars['votereward_config'] = $this->config->values('votereward_config', $this->session->userdata(['user' => 'server']));
                if($this->vars['votereward_config']['active'] == 1){
                    if($this->website->is_multiple_accounts() == true){
                        $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                    } else{
                        $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                    }
                    $this->load->model('account');
                    if($this->vars['votereward_config']['req_char'] == 1){
                        $this->vars['has_char'] = ($info = $this->Mcharacter->load_char_list()) ? $info : false;
                    }
                    if(!isset($this->vars['has_char']) || $this->vars['has_char'] != false){
                        $votelinks = $this->Maccount->load_vote_links();
                        $this->vars['content'] = [];
                        foreach($votelinks as $links){
                            if($links['api'] == 1){
                                $links['votelink'] = $links['votelink'] . '&amp;postback=' . $this->session->userdata(['user' => 'id']);
                                $countdown = $this->vars['votereward_config']['count_down'] + 20;
                            } else if($links['api'] == 3){
                                $links['votelink'] = $links['votelink'] . '&amp;pingUsername=' . $this->session->userdata(['user' => 'id']);
                                $countdown = $this->vars['votereward_config']['count_down'] + 20;
                            } else if($links['api'] == 4){
                                $links['votelink'] = $links['votelink'] . '-' . $this->session->userdata(['user' => 'id']);
                                $countdown = $this->vars['votereward_config']['count_down'] + 20;
                            } else if($links['api'] == 5){
                                $links['votelink'] = $links['votelink'] . '?incentive=' . $this->session->userdata(['user' => 'id']);
                                $countdown = $this->vars['votereward_config']['count_down'] + 20;
                            } else if($links['api'] == 6){
                                $links['votelink'] = str_replace('[USER]', $this->session->userdata(['user' => 'id']), $links['votelink']);
                                $countdown = $this->vars['votereward_config']['count_down'];
                            } else if($links['api'] == 8){
                                $links['votelink'] = $links['votelink'] . '?user=' . $this->session->userdata(['user' => 'id']);
                                $countdown = $this->vars['votereward_config']['count_down'] + 20;
                            } else if($links['api'] == 9){
                                $links['votelink'] = $links['votelink'] . '&amp;custom=' . $this->session->userdata(['user' => 'id']);
                                $countdown = $this->vars['votereward_config']['count_down'] + 20;
                            } else{
                                $countdown = $this->vars['votereward_config']['count_down'];
                            }
                            $check_last_vote = $this->Maccount->get_last_vote($links['id'], $links['hours'], $links['api'], $this->vars['votereward_config']['xtremetop_same_acc_vote'], $this->vars['votereward_config']['xtremetop_link_numbers']);
                            if($check_last_vote != false){
                                $this->vars['content'][] = ['id' => $links['id'], 'link' => $links['votelink'], 'name' => $links['name'], 'image' => $links['img_url'], 'voted' => 1, 'next_vote' => $this->Maccount->calculate_next_vote($check_last_vote, $links['hours']), 'api' => $links['api'], 'reward' => $links['reward'], 'reward_type' => $links['reward_type'], 'reward_sms' => ($links['api'] == 2) ? $links['mmotop_reward_sms'] : 0, 'countdown' => $countdown];
                            } else{
                                $this->vars['content'][] = ['id' => $links['id'], 'link' => $links['votelink'], 'name' => $links['name'], 'image' => $links['img_url'], 'voted' => 0, 'next_vote' => '', 'api' => $links['api'], 'reward' => $links['reward'], 'reward_type' => $links['reward_type'], 'reward_sms' => ($links['api'] == 2) ? $links['mmotop_reward_sms'] : 0, 'countdown' => $countdown];
                            }
                        }
                    }
                    if(defined('IS_GOOGLE_ADD_VOTE') && IS_GOOGLE_ADD_VOTE == true){
                        $this->vars['last_ads_vote'] = $this->Maccount->get_last_ads_vote(GOOGLE_ADD_TIME);
                    }
                    $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.votereward', $this->vars);
                } else{
                    $this->disabled();
                }
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM		
		public function two_factor_auth(){
			if($this->website->is_multiple_accounts() == true){
				$this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($_SESSION['tfa_temp_server'], true)]);
			} 
			else{
				$this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
			}
			$this->load->model('account');
			$this->vars['is_auth_enabled'] = $this->Maccount->check2FA($_SESSION['tfa_temp_user']);
			
			if($this->vars['is_auth_enabled'] != false){
				$tfa = new \RobThree\Auth\TwoFactorAuth;
				$_SESSION['2fa_secret'] = $this->vars['is_auth_enabled']['secret'];
				
				if(isset($_POST['check_2fa'])){
					if($tfa->verifyCode($_SESSION['2fa_secret'], $_POST['code'])){
						$this->Maccount->username = $_SESSION['tfa_temp_user'];
						$this->Maccount->password = $_SESSION['tfa_temp_password'];
						$this->Maccount->server = $_SESSION['tfa_temp_server'];
						$this->Maccount->servers = $_SESSION['tfa_temp_servers'];
						$login = $this->Maccount->login_user();
						$this->Maccount->log_user_ip();
						$this->Maccount->clear_login_attemts();
						$this->change_user_vip_session($_SESSION['tfa_temp_user'], $_SESSION['tfa_temp_server']);
						setcookie("DmN_Current_User_Server_" . $_SESSION['tfa_temp_user'], $_SESSION['tfa_temp_server'], strtotime('+1 days', time()), "/");	
						
						unset($_SESSION['2fa_secret'], $_SESSION['tfa_temp_user'], $_SESSION['tfa_temp_password'], $_SESSION['tfa_temp_server']);
						header('Location: '.$this->config->base_url.'account-panel');
					}
					else{
						$this->vars['tfa_error'] = __('The entered code is incorrect! Please retry.');
					}
				}
			}
			
			$this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.two_factor_auth', $this->vars);
		}

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM		
		public function reset_two_factor_auth(){
			if($this->website->is_multiple_accounts() == true){
				$this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($_SESSION['tfa_temp_server'], true)]);
			} 
			else{
				$this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
			}
			$this->load->model('account');
			$this->vars['is_auth_enabled'] = $this->Maccount->check2FA($_SESSION['tfa_temp_user']);
			
			if($this->vars['is_auth_enabled'] != false){
				if(isset($_POST['check_backup_code'])){
					if($this->vars['is_auth_enabled']['backup_code'] == $_POST['code']){
						$this->Maccount->remove2FA($_SESSION['tfa_temp_user']);
						unset($_SESSION['2fa_secret'], $_SESSION['tfa_temp_user'], $_SESSION['tfa_temp_password'], $_SESSION['tfa_temp_server']);
						$this->vars['tfa_success'] = __('Code has been reset, you can now login.');
					}
					else{
						$this->vars['tfa_error'] = __('The entered code is incorrect! Please retry.');
					}
				}
			}
			
			$this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.reset_two_factor_auth', $this->vars);
		}
		
		private function change_user_vip_session($user, $server)
        {
			$this->vars['config'] = $this->config->values('vip_config');
			
			if(!empty($this->vars['config']) && $this->vars['config']['active'] == 1){
				$this->load->model('account');
				if($this->vars['vip_data'] = $this->Maccount->check_vip($user, $server)){
					$this->vars['vip_package_info'] = $this->Maccount->load_vip_package_info($this->vars['vip_data']['viptype'], $server);
					if($this->vars['vip_data']['viptime'] <= time()){
						$this->Maccount->remove_vip($this->vars['vip_data']['viptype'], $user, $server);
						if($this->vars['vip_package_info'] != false){
							$this->Maccount->check_connect_member_file($this->vars['vip_package_info']['connect_member_load'], $user);
						}
					} else{
						$this->Maccount->set_vip_session($this->vars['vip_data']['viptime'], $this->vars['vip_package_info']);
					}
				} else{
					$this->session->unset_session_key('vip');
				}
			}
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function settings()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                $this->vars['config'] = $this->config->values('registration_config');
				
				if($this->website->is_multiple_accounts() == true){
					$this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
				} 
				else{
					$this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
				}
				$this->load->model('account');
				
                if(isset($_POST['recover_master_key'])){
                    if(!$this->Maccount->check_connect_stat()){
                        $this->vars['error'] = __('Please logout from game.');
                    } 
					else{
                        if(!$this->Maccount->recover_master_key_process()){
                            $this->vars['success'] = __('Your master key has been send to your email.');
                        } 
						else{
                            if(isset($this->Maccount->error)){
                                $this->vars['error'] = $this->Maccount->error;
                            } 
							else{
                                $this->vars['error'] = __('Unable to recover master key.');
                            }
                        }
                    }
                }
				
				if(defined('GOOGLE_2FA') && GOOGLE_2FA == true){
					$this->vars['is_auth_enabled'] = $this->Maccount->check2FA($this->session->userdata(['user' => 'username']));
					
					if($this->vars['is_auth_enabled'] == false){
						$tfa = new \RobThree\Auth\TwoFactorAuth;
						
						if(!isset($_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])])){
							$_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])] = $tfa->createSecret(160);
						}
						
						if(!isset($_SESSION['2fa_backup_code'][$this->session->userdata(['user' => 'username'])])){
							$_SESSION['2fa_backup_code'][$this->session->userdata(['user' => 'username'])] = trim(chunk_split(bin2hex(openssl_random_pseudo_bytes(16)), 4, '-'), '-');
						}
						
						$this->vars['backup_code'] = $_SESSION['2fa_backup_code'][$this->session->userdata(['user' => 'username'])];

						$this->vars['qr_image'] = $tfa->getQRCodeImageAsDataUri($this->config->config_entry('main|servername').' '.trim($this->session->userdata(['user' => 'username'])), $_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])]);

						if(isset($_POST['enable_2fa'])){
							if($tfa->verifyCode($_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])], $_POST['code'])){
								$this->Maccount->add2FA($this->session->userdata(['user' => 'username']), $_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])], $this->vars['backup_code']);
								unset($_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])], $_SESSION['backup_code'][$this->session->userdata(['user' => 'username'])]);
								$this->vars['is_auth_enabled'] = $this->Maccount->check2FA($this->session->userdata(['user' => 'username']));
								$this->vars['tfa_success'] = __('You can now log-in with two factor authentication!');
							}
							else{
								$this->vars['tfa_error'] = __('The entered code is incorrect! Please retry.');
							}
						}
					}
					else{
						$tfa = new \RobThree\Auth\TwoFactorAuth;
						$_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])] = $this->vars['is_auth_enabled']['secret'];
						$_SESSION['backup_code'][$this->session->userdata(['user' => 'username'])] = $this->vars['is_auth_enabled']['backup_code'];
						
						if(isset($_POST['disable_2fa'])){
							if($tfa->verifyCode($_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])], $_POST['code'])){
								$this->Maccount->remove2FA($this->session->userdata(['user' => 'username']));
								unset($_SESSION['2fa_secret_code'][$this->session->userdata(['user' => 'username'])], $_SESSION['backup_code'][$this->session->userdata(['user' => 'username'])]);
								header('Location: '.$this->config->base_url.'settings');
							}
							else{
								$this->vars['tfa_error'] = __('The entered code is incorrect! Please retry.');
							}
						}
					}
				}
				
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.settings', $this->vars);
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function email_confirm($code)
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                $code = strtolower(trim(preg_replace('/[^0-9a-f]/i', '', $code)));
                $this->vars['set_new_email'] = false;
                if(strlen($code) <> 40){
                    $this->vars['error'] = __('Invalid email confirmation code');
                } else{
                    $data = $this->Maccount->load_email_confirmation_by_code($code);
                    if($data){
                        if($data['old_email'] == 0){
                            if($this->Maccount->update_email($data['account'], $data['email'])){
                                $this->Maccount->delete_old_confirmation_entries($data['account']);
                                $this->vars['success'] = __('Email address successfully updated.');
                            }
                        } else{
                            $this->vars['set_new_email'] = true;
                        }
                    } else{
                        $this->vars['error'] = __('Confirmation code does not exist in database.');
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.set_new_email', $this->vars);
            } else{
                $this->login();
            }
        }

        public function exchange_wcoins()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                $this->vars['wcoin_config'] = $this->config->values('wcoin_exchange_config', $this->session->userdata(['user' => 'server']));
                if($this->vars['wcoin_config'] != false && $this->vars['wcoin_config']['active'] == 1){
                    $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.exchange_wcoins', $this->vars);
                } else{
                    $this->disabled();
                }
            } else{
                $this->login();
            }
        }

        public function logs($page = 1)
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                $this->load->lib("pagination");
                $this->vars['logs'] = $this->Maccount->load_logs($page, $this->config->config_entry('account|account_logs_per_page'));
                $this->pagination->initialize($page, $this->config->config_entry('account|account_logs_per_page'), $this->Maccount->count_total_logs(), $this->config->base_url . 'account-panel/logs/%s');
                $this->vars['pagination'] = $this->pagination->create_links();
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.logs', $this->vars);
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function zen_wallet()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                if(!$this->Maccount->check_connect_stat())
                    $this->vars['error'] = __('Please logout from game.'); 
				else{
                    $this->load->model('warehouse');
                    if($this->Mwarehouse->get_vault_content()){
                        $this->vars['wh_zen'] = $this->Mwarehouse->vault_money;
                    }
                    $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                    $this->vars['wallet_zen'] = $this->Maccount->load_wallet_zen();
					if(!$this->vars['wallet_zen']){
                        $this->vars['wallet_zen']['credits3'] = 0;
                    }							   
                    if(isset($_POST['transfer_zen'])){
                        $from = trim(isset($_POST['from']) ? $_POST['from'] : '');
                        $to = trim(isset($_POST['to']) ? $_POST['to'] : '');
                        $amount = trim(isset($_POST['zen']) ? $_POST['zen'] : '');
                        if($from == '')
                            $this->vars['error'] = __('You didn\'t select from where you want to send zen'); 
						else{
                            if($to == '')
                                $this->vars['error'] = __('You didn\'t select to where you want to send zen'); 
							else{
                                if(!preg_match('/^[0-9]+$/', $amount))
                                    $this->vars['error'] = __('Amount of zen you insert is invalid.'); 
								else{
                                    if($from == $to){
                                        $this->vars['error'] = vsprintf(__('You can\'t send zen from %s to %s'), [$from, $to]);
                                    } else{
                                        if($from == 'webwallet'){
                                            if($this->vars['wallet_zen']['credits3'] < $amount)
                                                $this->vars['error'] = __('Amount of zen in your web wallet is too low.');
                                        } else if($from == 'warehouse'){
                                            if($this->vars['wh_zen'] < $amount)
                                                $this->vars['error'] = __('Amount of zen in your warehouse is too low.');
                                        } else{
                                            if($this->Mcharacter->check_char($from)){
                                                if($this->Mcharacter->char_info['Money'] < $amount)
                                                    $this->vars['error'] = sprintf(__('Amount of zen on %s is too low.'), $from);
                                            } else{
                                                $this->vars['error'] = __('Character not found.');
                                            }
                                        }
                                        if(!isset($this->vars['error'])){
                                            if($to == 'webwallet'){
                                                $this->website->add_credits($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $amount, 4, false, $this->session->userdata(['user' => 'id']));
                                                if($from == 'warehouse'){
                                                    $this->Mwarehouse->decrease_zen($this->session->userdata(['user' => 'username']), $amount);
                                                } else{
                                                    $this->Mcharacter->decrease_zen($this->session->userdata(['user' => 'username']), $amount, $from);
                                                }
                                            } else if($to == 'warehouse'){
                                                if($amount > $this->config->config_entry('account|max_ware_zen'))
                                                    $this->vars['error'] = sprintf(__('Max zen than can be send to warehouse is %s'), $this->website->zen_format($this->config->config_entry('account|max_ware_zen'))); else{
                                                    if(((int)$amount + $this->vars['wh_zen']) > $this->config->config_entry('account|max_ware_zen'))
                                                        $this->vars['error'] = __('Your warehouse zen limit exceeded. Try to transfer lower amount.'); 
													else{
                                                        $this->Mwarehouse->add_zen($this->session->userdata(['user' => 'username']), $amount);
                                                        if($from == 'webwallet'){
															$this->website->charge_credits($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $amount, 4, $this->session->userdata(['user' => 'id']));
                                                        } else{
                                                            $this->Mcharacter->decrease_zen($this->session->userdata(['user' => 'username']), $amount, $from);
                                                        }
                                                    }
                                                }
                                            } 
											else{
                                                if($amount > $this->config->config_entry('account|max_char_zen'))
                                                    $this->vars['error'] = sprint(__('Max zen than can be send to character is %s'), $this->website->zen_format($this->config->config_entry('account|max_char_zen'))); else{
                                                    $this->Mcharacter->check_char($to);
                                                    if(((int)$amount + $this->Mcharacter->char_info['Money']) > $this->config->config_entry('account|max_char_zen'))
                                                        $this->vars['error'] = __('Your character zen limit exceeded. Try to transfer lower amount.'); 
													else{
                                                        $this->Mcharacter->add_zen($this->session->userdata(['user' => 'username']), $amount, $to);
                                                        if($from == 'webwallet'){
                                                            $this->website->charge_credits($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $amount, 4, $this->session->userdata(['user' => 'id']));
                                                        } else if($from == 'warehouse'){
                                                            $this->Mwarehouse->decrease_zen($this->session->userdata(['user' => 'username']), $amount);
                                                        } else{
                                                            $this->Mcharacter->decrease_zen($this->session->userdata(['user' => 'username']), $amount, $from);
                                                        }
                                                    }
                                                }
                                            }
                                            if(!isset($this->vars['error'])){
                                                $this->vars['success'] = __('Zen was successfully transferred.');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.zen_wallet', $this->vars);
            } else{
                $this->login();
            }
        }

        public function my_referral_list()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                $this->vars['my_referral_list'] = $this->Maccount->load_my_referrals();
                if(!empty($this->vars['my_referral_list'])){
                    foreach($this->vars['my_referral_list'] as $key => $referrals){
                        $this->vars['my_referral_list'][$key]['ref_chars'] = $this->Mcharacter->load_chars_from_ref($referrals['refferal'], $this->session->userdata(['user' => 'server']));
                    }
                    $this->vars['ref_rewards'] = $this->Maccount->load_referral_rewards();
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.my_referral_list', $this->vars);
            } else{
                $this->login();
            }
        }

        public function get_reward()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                if(isset($_POST['get_reward'])){
                    if(!$this->Mcharacter->check_reward()){
                        $this->Mcharacter->log_reward();
                        $this->Mcharacter->add_reward();
                        $this->vars['success'] = 'You have received 450 Wcoins for free.';
                    } else{
                        $this->vars['error'] = 'You have been already rewarded on this server';
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.get_reward', $this->vars);
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function exchange_online()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->load->model('account');
                $this->vars['online_time'] = $this->Maccount->load_online_hours();
                if($this->vars['online_time'] == false){
                    $this->vars['online_time'] = 0;
                    $this->vars['minutes_left'] = 0;
                } else{
                    $this->vars['OnlineMinutes'] = $this->vars['online_time']['OnlineMinutes'];
                    $this->vars['online_time'] = floor($this->vars['OnlineMinutes'] / 60);
                    $this->vars['minutes_left'] = $this->vars['OnlineMinutes'] - (floor($this->vars['OnlineMinutes'] / 60) * 60);
                }
                if(isset($_POST['trade_hours'])){
                    if(!$this->Maccount->check_connect_stat())
                        $this->vars['error'] = __('Please logout from game.'); 
					else{
                        if($this->vars['online_time'] <= 0)
                            $this->vars['error'] = __('You don\'t have online time on this server'); 
						else{
                            if($this->Maccount->exchange_online_hours($this->vars['online_time'], $this->vars['minutes_left'])){
                                $this->vars['success'] = 'Online time successfully exchanged';
                                $this->vars['online_time'] = 0;
                            } else{
                                $this->vars['error'] = __('Unable to exchange online time');
                            }
                        }
                    }
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.exchange_online', $this->vars);
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM		
		public function exchange_lucky_coins()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
                if($this->website->is_multiple_accounts() == true){
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($this->session->userdata(['user' => 'server']), true)]);
                } else{
                    $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                }
                $this->vars['coin_config'] = $this->config->values('luckycoin_config', $this->session->userdata(['user' => 'server']));
                if($this->vars['coin_config']['active'] == 1){
                    unset($this->vars['coin_config']['active']);
                    $this->load->model('account');
                    $this->load->lib('iteminfo');
                    $this->load->model('shop');
                    if(isset($_POST['exchange_coins'])){
                        foreach($_POST as $key => $value){
                            $this->Mcharacter->$key = trim($value);
                        }
                        if(!$this->Maccount->check_connect_stat())
                            $this->vars['error'] = __('Please logout from game.'); else{
                            if(!isset($this->Mcharacter->vars['character']))
                                $this->vars['error'] = __('Invalid Character'); else{
                                if(!$this->Mcharacter->check_char())
                                    $this->vars['error'] = __('Character not found.'); else{
                                    if(!isset($this->Mcharacter->vars['lucky_coin']))
                                        $this->vars['error'] = __('Please select option for exchange.'); else{
                                        if(!in_array($this->Mcharacter->vars['lucky_coin'], [10, 20, 30]))
                                            $this->vars['error'] = __('Invalid exchange option selected.'); else{
                                            $this->vars['coin_data'] = $this->Mcharacter->check_amount_of_coins();
                                            if(array_sum($this->vars['coin_data']) < $this->Mcharacter->vars['lucky_coin']){
                                                $this->vars['error'] = __('You have insufficient amount of coins.');
                                            } else{
                                                if(array_key_exists($this->Mcharacter->vars['lucky_coin'], $this->vars['coin_config'])){
                                                    $max_prob = $this->vars['coin_config'][$this->Mcharacter->vars['lucky_coin']]['max_probability'];
                                                    unset($this->vars['coin_config'][$this->Mcharacter->vars['lucky_coin']]['max_probability']);
                                                    $chance = $this->Mcharacter->draw_chance($this->vars['coin_config'][$this->Mcharacter->vars['lucky_coin']], $max_prob);
                                                    shuffle($this->vars['coin_config'][$this->Mcharacter->vars['lucky_coin']][$chance]);
                                                    $item_key = array_rand($this->vars['coin_config'][$this->Mcharacter->vars['lucky_coin']][$chance], 1);
                                                    $cat = $this->vars['coin_config'][$this->Mcharacter->vars['lucky_coin']][$chance][$item_key][1];
                                                    $id = $this->vars['coin_config'][$this->Mcharacter->vars['lucky_coin']][$chance][$item_key][0];
                                                    $new_coins = array_sum($this->vars['coin_data']) - $this->Mcharacter->vars['lucky_coin'];
                                                    $this->iteminfo->setItemData($id, $cat, $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size'));
                                                    $data = $this->iteminfo->item_data;
                                                    if($vault = $this->Mshop->get_vault_content()){
                                                        $space = $this->Mshop->check_space($vault['Items'], $data['x'], $data['y'], $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'wh_multiplier'), $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size'), $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'wh_hor_size'), $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'wh_ver_size'));
                                                        if($space === null){
                                                            $this->vars['error'] = $this->Mshop->errors[0];
                                                        } else{
                                                            $item_data = $this->vars['coin_config'][$this->Mcharacter->vars['lucky_coin']][$chance][$item_key];
                                                            if(is_array($item_data[5])){
                                                                $lvl = rand($item_data[5][0], $item_data[5][1]);
                                                            } else{
                                                                $lvl = $item_data[5];
                                                            }
                                                            if($item_data[6] == -1){
                                                                $skill = rand(0, 1);
                                                            } else{
                                                                $skill = $item_data[6];
                                                            }
                                                            if($item_data[7] == -1){
                                                                $luck = rand(0, 1);
                                                            } else{
                                                                $luck = $item_data[7];
                                                            }
                                                            if(is_array($item_data[8])){
                                                                $opt = rand($item_data[8][0], $item_data[8][1]);
                                                            } else{
                                                                $opt = $item_data[8];
                                                            }
                                                            if($this->Mcharacter->remove_old_coins($this->vars['coin_data'])){
                                                                $this->load->lib("createitem", [MU_VERSION, SOCKET_LIBRARY]);
                                                                if($new_coins > 0){
                                                                    if($new_coins > 255){
                                                                        $coins_left = [];
                                                                        while($new_coins >= 255){
                                                                            $new_coins -= 255;
                                                                            if($new_coins >= 255){
                                                                                $coins_left[] = 255;
                                                                            } else{
                                                                                $coins_left[] = $new_coins;
                                                                            }
                                                                        }
                                                                        $coins_left[] = 255;
                                                                        $i = -1;
                                                                        if($this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size') == 64){
                                                                            $serial2 = true;
                                                                        }
                                                                        foreach($this->vars['coin_data'] AS $key => $value){
                                                                            $i++;
                                                                            if(array_key_exists($i, $coins_left)){
                                                                                $this->vars['coin_data'][$key] = $this->createitem->make(100, 14, false, [], $coins_left[$i], array_values($this->Mshop->generate_serial())[0], $serial2)->to_hex();
                                                                            } else{
                                                                                $this->vars['coin_data'][$key] = str_pad("", $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size'), "F");
                                                                            }
                                                                        }
                                                                        $this->Mcharacter->add_multiple_new_coins($this->vars['coin_data']);
                                                                    } else{
                                                                        $new_coin_item = $this->createitem->make(100, 14, false, [], $new_coins, array_values($this->Mshop->generate_serial())[0], $serial2)->to_hex();
                                                                        $this->Mcharacter->add_new_coins(array_keys($this->vars['coin_data'])[0], $new_coin_item);
                                                                    }
                                                                }
																//$this->createitem->make($id, $cat, false, [], $new_coins, array_values($this->Mshop->generate_serial())[0], $serial2)->to_hex()
                                                                // $this->Mshop->generate_new_items($this->Mshop->generate_item_hex($id, $cat, $item_data[2], $item_data[3], $item_data[4], $lvl, $skill, $luck, $opt, $item_data[9], $item_data[10], $item_data[11], $item_data[12]), $space, $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'wh_multiplier'), $this->website->get_value_from_server($this->session->userdata(['user' => 'server']), 'item_size'));
                                                                $this->Maccount->add_account_log('Exchanged ' . $this->Mcharacter->vars['lucky_coin'] . ' Lucky coins to ' . $data['name'] . '', 0, $this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']));
                                                                $this->Mshop->update_warehouse();
                                                                $this->vars['success'] = sprintf(__('Coins successfully exchanged To %s.'), $data['name']);
                                                            } else{
                                                                $this->vars['error'] = __('Unable to remove lucky coins from inventory.');
                                                            }
                                                        }
                                                    } else{
                                                        $this->vars['error'] = __('Please open your warehouse in game first.');
                                                    }
                                                } else{
                                                    $this->vars['error'] = __('Configuration element not found.');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $this->vars['char_list'] = $this->Mcharacter->load_char_list();
                    $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.exchnage_lucky_coin', $this->vars);
                } else{
                    $this->disabled();
                }
            } else{
                $this->login();
            }
        }

        public function login()
        {
            $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.login');
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function login_with_facebook()
        {
            $this->load->lib('fb');
            $this->fb->check_fb_user();
            if(isset($_SESSION['fb_access_token'])){
                $email = $this->fb->getEmail();
                try{
                    if($this->website->is_multiple_accounts() == true){
                        if(isset($_POST['server'])){
                            $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_db_from_server($_POST['server'], true)]);
                            $this->load->model('account');
                            if($info = $this->Maccount->check_fb_user($email, $_POST['server'])){
                                $this->Maccount->clear_login_attemts();
                                header('Location: ' . $this->config->base_url . 'account-panel');
                            } else{
                                $this->fb_register($_POST['server'], $this->vars['user_profile']['email']);
                            }
                        }
                        $this->load->view($this->config->config_entry('main|template') . DS . 'account_panel' . DS . 'view.fb_login');
                    } else{
                        $this->load->lib(['account_db', 'db'], [HOST, USER, PASS, $this->website->get_default_account_database()]);
                        $this->load->model('account');
                        $server_list = array_keys($this->website->server_list());
                        if($info = $this->Maccount->check_fb_user($this->fb->getEmail(), $server_list[0])){
                            $this->Maccount->clear_login_attemts();
                            header('Location: ' . $this->config->base_url . 'account-panel');
                        } else{
                            header('Location: ' . $this->config->base_url . 'registration/create-account-with-fb/' . $server_list[0] . '/' . urlencode($email));
                        }
                    }
                } catch(FacebookApiException $e){
                    unset($_SESSION['fb_access_token']);
                    throw new exception($e->getMessage());
                }
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM		
		public function coupons()
        {
            if($this->session->userdata(['user' => 'logged_in'])){
               
                if(isset($_POST['redeem_coupon'])){
                    $this->load->model('account');
					$this->load->model('partner');
					
					$coupon = isset($_POST['coupon']) ? $_POST['coupon'] : '';
						
					if($coupon == ''){
						$this->vars['error'] = __('Please enter coupon code.');
					} else{
						$checkCoupon = $this->Mpartner->checkCoupon($coupon);
						if($checkCoupon == false){
							$this->vars['error'] = __('Coupon code not found.');
						} else{
							if($checkCoupon['code_count'] <= 0){
								$this->vars['error'] = __('Coupon code already used.');
							}
							else{
								if($checkCoupon['username'] == $this->session->userdata(['user' => 'username'])){
									$this->vars['error'] = __('Cannot activate coupon by yourself.');
								}
								else{
									$log = $this->Mpartner->findCouponLog($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $coupon);
									if($log != false)
										$this->vars['error'] = __('You have already activated this coupon.');
									else{
										$this->Mpartner->setCouponUsed($coupon);
										$this->Mpartner->logCoupon($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $coupon, $checkCoupon['username']);
										$this->website->add_credits($this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']), $checkCoupon['amount'], $checkCoupon['type']);
										$this->Maccount->add_account_log('Activate Coupon: '.$coupon.', Reward' . $this->website->translate_credits($checkCoupon['type'], $this->session->userdata(['user' => 'server'])) . '', $checkCoupon['amount'], $this->session->userdata(['user' => 'username']), $this->session->userdata(['user' => 'server']));
										$this->vars['success'] = __('Coupon successfully activated.');
									}
								}
							}
						}
					}
                    
                }
                $this->load->view($this->config->config_entry('main|template') . DS . 'partner' . DS . 'view.coupon', $this->vars);
            } else{
                $this->login();
            }
        }

		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM
        public function logout()
        {
            $email = $this->session->userdata(['user' => 'email']);
            $id = $this->session->userdata(['user' => 'ipb_id']);
            $this->session->unset_session_key('user');
			$this->session->unset_session_key('vip');
            if(defined('IPS_CONNECT') && IPS_CONNECT == true){
                $this->load->lib('ipb');
                if($this->ipb->checkEmail($email) == true){
                    $this->ipb->crossLogout($id, $this->config->base_url);
                } else{
                    header('Location: ' . $this->config->base_url);
                }
            } else{
                header('Location: ' . $this->config->base_url);
            }
        }

        public function disabled()
        {
            $this->load->view($this->config->config_entry('main|template') . DS . 'view.module_disabled');
        }
    }