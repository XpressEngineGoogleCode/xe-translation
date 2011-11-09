<?php
    /**
     * @class  translationModel
     * @author NHN (developers@xpressengine.com)
     * @brief  translation module Model class
     **/

    class translationModel extends module {

		/**
		 * @brief initialization
		 **/
		function init() {
		}

		/**
		 * @brief get member project list
		 **/
		function getMemberProjectList($args){
			if(!$args->member_srl || !$args->module_srl) return;

			$oModuleModel = &getModel('module');
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);

			$output = executeQueryArray('translation.getMemberProjectList',$args);

			if(!$output->data) return null;
			else return $output->data;
		}

		/**
		 * @brief get all project list
		 **/
		function getProjectList($module_srl){
			if(!$module_srl) return;

			$oModuleModel = &getModel('module');
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);

			$args->module_srl = $module_srl;
			$output = executeQueryArray('translation.getProjectList',$args);

			if(!$output->data) return null;
			else return $output->data;
		}

		/**
		 * @brief get project info
		 **/
		function getProject($translation_project_srl){
			if(!$translation_project_srl) return;

			$args->translation_project_srl = $translation_project_srl;
			$output = executeQuery('translation.getProject',$args);

			if(!$output->data) return null;
			else return $output->data;
		}

		/**
		 * @brief get file list by project srl
		 **/
		function getProjectFileList($args){
			if(!$args->translation_project_srl || !$args->module_srl) return;

			$output = executeQueryArray('translation.getProjectFileList',$args);

			if(!$output->data) return null;
			else return $output->data;
		}

		/**
		 * @brief get file info
		 **/
		function getFile($translation_file_srl){
			if(!$translation_file_srl) return;

			$args->translation_file_srl = $translation_file_srl;
			$output = executeQuery('translation.getFile',$args);

			if(!$output->data) return null;
			else return $output->data;
		}

		/**
		 * @brief get content based on the source lang
		 **/
		function getSourceContents($source_lang, $target_lang, $translation_file_srl){

			if(!$translation_file_srl) return;

			$args->translation_file_srl = $translation_file_srl;
			$args->lang = $source_lang;
			$args->is_original = 1;

			$output = executeQuery('translation.getSourceLangFileContents',$args);
			if(!$output->toBool()) {return $output;}

			$source_data = $output->data;
			$source_contents = array();
			
			if($source_data){
				foreach($source_data as $key => $val){
					$source_contents[$key]['source'] = $val;
					$target_contents = $this->getTargetContents($val->content_node,$target_lang,$translation_file_srl);
					$source_contents[$key]['target'] = $target_contents;	
					$target_count = count($target_contents);
					$source_contents[$key]['target_count'] = $target_count;	
				}
			}
			$this->multi2dSortAsc($source_contents,'target_count');
			
			return $source_contents;
		}

		/**
		 * @brief get target lang contens
		 **/
		function getTargetContents($content_node,$target_lang,$translation_file_srl){
			$obj->content_node = $content_node;
			$obj->lang = $target_lang;
			$obj->translation_file_srl = $translation_file_srl;

			$output = executeQuery('translation.getTargetLangFileContents', $obj);

			return $output->data;
		}

		function getFileAllContents($translation_file_srl){
			if(!$translation_file_srl) return;
			$content_nodes = $this->getFileContentNodes($translation_file_srl);

			// get supported language list
			$lang_supported_list = Context::loadLangSupported();
			
			$valueArr = array();
			foreach($content_nodes as $key => $val){
				$obj->content_node = $val->content_node;
				$obj->translation_file_srl = $val->translation_file_srl;
				
				foreach($lang_supported_list as $lang_key => $lang_val){
					$obj->lang = $lang_key;
					$value = $this->getRecommendValue($obj);

					if($value && $value->is_original!=1){
						$vArr['content_node'] = $obj->content_node;
						$vArr['lang'] = $obj->lang;
						$vArr['content'] = $value->content;
						$vArr['is_new_lang'] = $value->is_new_lang;

						array_push($valueArr, $vArr);
					}
				}
			}

			//var_dump($valueArr);

			$file_info = $this->getFile($translation_file_srl);
			$oXMLContext = new XMLContext($file_info->target_file, "en");

			
			$xmlContents = $oXMLContext->getXmlFile($valueArr,$file_info->file_type);
			//$xml = new SimpleXMLElement($xmlContents);
			//$xmlContents = $xml->asXML();
			
			return $xmlContents;
		}

		function getFileContentNodes($translation_file_srl){
			if(!$translation_file_srl) return;
			$args->translation_file_srl = $translation_file_srl;
			$args->is_original = 1;

			$output = executeQuery('translation.getFileContentNodes',$args);
			if(!$output->toBool()) {return $output;}

			return $output->data;
		}

		function getRecommendValue($obj){
			$args->translation_file_srl = $obj->translation_file_srl;
			$args->content_node = $obj->content_node;
			$args->lang = $obj->lang;
				
			$output = executeQuery('translation.getMaxRecommendCount',$args);
			if(!$output->toBool()) {$args->max_recomment_count = 0;} else {$args->max_recomment_count = intval($output->data->max_recommended_count);}

			$output = executeQuery('translation.getRecommendValue',$args);
			if(!$output->toBool()) {return null;} 

			return $output->data;
		}

		function getDefaultTargetContents($args){
			if(!$args->translation_file_srl || !$args->content_node || !$args->lang) return;

			$obj->translation_file_srl = $args->translation_file_srl;
			$obj->content_node = $args->content_node;
			$obj->lang = $args->lang;
			$obj->is_original = 1;
			$output = executeQuery('translation.getDefaultTargetContents',$obj);

			if(!$output->toBool()) {return null;} 
			return $output->data;
		}

		function multi2dSortAsc(&$arr, $key){
			$sort_col = array();
			foreach ($arr as $sub) $sort_col[] = $sub[$key];
			array_multisort($sort_col, $arr);
		}


		function downloadFile($filepath){
			if(file_exists($filepath)){
				if ($fd = fopen ($filepath, "r")) {
					$fsize = filesize($filepath);
					$path_parts = pathinfo($filepath);
					$ext = strtolower($path_parts["extension"]);
					switch ($ext) {
						case "pdf":
						header("Content-type: application/pdf"); // add here more headers for diff. extensions
						header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
						break;
						default;
						header("Content-type: application/octet-stream");
						header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
					}
					header("Content-length: $fsize");
					header("Cache-control: private"); //use this to open files directly
					while(!feof($fd)) {
						$buffer = fread($fd, 2048);
						echo $buffer;
					}
				}
				fclose ($fd);
			}
		}

	}
?>
