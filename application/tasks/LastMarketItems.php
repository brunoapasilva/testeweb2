<?php

    class LastMarketItems extends Job
    {
        private $registry, $config, $load, $items = [], $server_list;

        public function __construct()
        {
            $this->registry = controller::get_instance();
            $this->config = $this->registry->config;
            $this->load = $this->registry->load;
            $this->load->helper('website');
            $this->load->model('market');
            $this->load->lib('iteminfo');
            $this->load->lib('itemimage');
            $this->server_list = $this->registry->website->server_list();
        }
		
		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM 
        public function execute()
        {
            if($this->config->config_entry('modules|last_market_items_module') == 1){
                foreach($this->server_list AS $key => $server){
                    $this->lastAdded($key);
					$this->lastSold($key);
                }
            }
            return true;
        }
		
		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM 
		private function lastAdded($server){
			$stmt = $this->registry->website->db('web')->prepare('SELECT TOP ' . $this->registry->website->db('web')->sanitize_var((int)$this->config->config_entry('modules|last_market_items_count')) . ' id, cat, item, price_type, price, price_jewel, jewel_type FROM DmN_Market WHERE active_till > GETDATE() AND sold != 1 AND removed != 1 AND server = :server ORDER BY add_date DESC');
			$stmt->execute([':server' => $server]);
			$items = $stmt->fetch_all();
			if(!empty($items)){
				foreach($items as $value){
					$this->registry->iteminfo->itemData($value['item']);
					if($value['price_jewel'] != 0 && $value['jewel_type'] != 0){
						$price = $this->registry->Mmarket->get_jewel_image($value['jewel_type'], 'width:22px;height:17px;') . 'x ' . $value['price_jewel'];
					} else{
						$price = $this->registry->website->zen_format(round(($value['price'] / 100) * $this->config->config_entry('market|sell_tax') + $value['price'])) . ' ' . $this->registry->website->translate_credits($value['price_type'], $server);
					}
					$this->items[] = [
						'id' => $value['id'], 
						'name' => $this->registry->iteminfo->getNameStyle(true, 20), 
						'price' => $price, 
						'item' => $value['item'], 
						'namenostyle' => $this->registry->iteminfo->realName(), 
						'image' => $this->registry->itemimage->load($this->registry->iteminfo->id, $this->registry->iteminfo->type, substr($this->registry->iteminfo->GetLevel(), 1), 0),
						'item_info' => $this->registry->itemimage->load($this->registry->iteminfo->id, $this->registry->iteminfo->type, (int)substr($this->registry->iteminfo->getLevel(), 1)) . '<br />' . $this->registry->iteminfo->allInfo()
					];
				}
				$this->registry->website->set_cache('last_market_' . $server, $this->items, 3600);
				$this->items = [];
			}
			else{
				$this->registry->website->set_cache('last_market_' . $server, [], 3600);
			}
		}
		
		// @ioncube.dk cmsVersion('g8LU2sewjnwUpNnBTm9t85c3Xgf/0Y9V+rZWvw94O3A=', '009869451363953188238779430856374927754') -> "NewDmNIonCubeDynKeySecurityAlgo" RANDOM 
		private function lastSold($server){
			$stmt = $this->registry->website->db('web')->prepare('SELECT TOP ' . $this->registry->website->db('web')->sanitize_var((int)$this->config->config_entry('modules|last_market_items_count')) . ' id, cat, item, price_type, price, price_jewel, jewel_type FROM DmN_Market WHERE sold = 1 AND server = :server ORDER BY add_date DESC');
			$stmt->execute([':server' => $server]);
			$items = $stmt->fetch_all();
			if(!empty($items)){
				foreach($items as $value){
					$this->registry->iteminfo->itemData($value['item']);
					if($value['price_jewel'] != 0 && $value['jewel_type'] != 0){
						$price = $this->registry->Mmarket->get_jewel_image($value['jewel_type'], 'width:22px;height:17px;') . 'x ' . $value['price_jewel'];
					} else{
						$price = $this->registry->website->zen_format(round(($value['price'] / 100) * $this->config->config_entry('market|sell_tax') + $value['price'])) . ' ' . $this->registry->website->translate_credits($value['price_type'], $server);
					}
					$this->items[] = [
						'id' => $value['id'], 
						'name' => $this->registry->iteminfo->getNameStyle(true, 20), 
						'price' => $price, 
						'item' => $value['item'], 
						'namenostyle' => $this->registry->iteminfo->realName(), 
						'image' => $this->registry->itemimage->load($this->registry->iteminfo->id, $this->registry->iteminfo->type, substr($this->registry->iteminfo->GetLevel(), 1), 0),
						'item_info' => $this->registry->itemimage->load($this->registry->iteminfo->id, $this->registry->iteminfo->type, (int)substr($this->registry->iteminfo->getLevel(), 1)) . '<br />' . $this->registry->iteminfo->allInfo()
					];
				}
				$this->registry->website->set_cache('last_sold_market_' . $server, $this->items, 3600);
				$this->items = [];
			}
			else{
				$this->registry->website->set_cache('last_sold_market_' . $server, [], 3600);
			}
		}
    }