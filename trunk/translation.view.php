<?php
    /**
     * @class  translationView
     * @author NHN (developers@xpressengine.com)
     * @brief  translation module View class
     **/

    class translationView extends translation {
        /**
         * @brief initialize translation view class.
         **/
		function init() {
           /**
             * get skin template_path
             * if it is not found, default skin is xe_contact
             **/
            $template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
            if(!is_dir($template_path)||!$this->module_info->skin) {
                $this->module_info->skin = 'xe_translation_official';
                $template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
            }

			$oTranslationModel =  &getModel('translation');
			$translation_project_srl = Context::get('translation_project_srl');

			if($translation_project_srl){
				$project_info = $oTranslationModel->getProject($translation_project_srl);
				Context::set('project_info',$project_info);
			}

			$translation_file_srl = Context::get('translation_file_srl');

			if($translation_file_srl){
				$file_info =  $oTranslationModel->getFile($translation_file_srl);
				Context::set('file_info',$file_info);
			}

            $this->setTemplatePath($template_path);
		}

        /**
         * @brief display translation index page
         **/
        function dispTranslationIndex() {

			// set template_file to be index.html
            $this->setTemplateFile('index');
        }

        /**
         * @brief display translation register project page
         **/
        function dispTranslationRegProject() {

			$translation_project_srl = Context::get('translation_project_srl');

			$oTranslationModel =  &getModel('translation');
			$project_info = $oTranslationModel->getProject($translation_project_srl);

			Context::set('project_info',$project_info);

			// set template_file to be register_project.html
            $this->setTemplateFile('register_project');
        }

        /**
         * @brief display translation (member) project list page
         **/
		function dispTranslationProjectList() {
			
			// get member_srl
            $member_srl = Context::get('member_srl');

			$oTranslationModel =  &getModel('translation');
			$obj->module_srl = $this->module_info->module_srl;
			
			if($member_srl){
				$obj->member_srl = $member_srl;
				$project_list =  $oTranslationModel->getMemberProjectList($obj);
			}else{
				$project_list =  $oTranslationModel->getProjectList($obj->module_srl);
			}

			Context::set('project_list',$project_list);

			// set template_file to be project_list.html
			$this->setTemplateFile('project_list');
		}

        /**
         * @brief display translation (project) file list page
         **/
		function dispTranslationFileList() {
			
			// get member_srl
            $translation_project_srl = Context::get('translation_project_srl');
			$obj->module_srl = $this->module_info->module_srl;

			if($translation_project_srl){
				$oTranslationModel =  &getModel('translation');
				$project_info = $oTranslationModel->getProject($translation_project_srl);
				Context::set('project_info',$project_info);
				$obj->translation_project_srl = $project_info->translation_project_srl;
				$file_list = $oTranslationModel->getProjectFileList($obj);
			}else{

			}

			Context::set('file_list',$file_list);

			// set template_file to be file_list.html
			$this->setTemplateFile('file_list');
		}

        /**
         * @brief display translation register file page
         **/
        function dispTranslationRegFile() {

			$translation_project_srl = Context::get('translation_project_srl');
			$translation_file_srl = Context::get('translation_file_srl');

			$oTranslationModel =  &getModel('translation');

			
			if($translation_file_srl){
				$file_info =  $oTranslationModel->getFile($translation_file_srl);
				$translation_project_srl = $file_info->translation_project_srl;
			}
			
			// get project info
			$project_info = $oTranslationModel->getProject($translation_project_srl);

			// get project list
			$obj->module_srl = $this->module_info->module_srl;
			$project_list = $oTranslationModel->getProjectList($obj->module_srl);

			Context::set('project_list',$project_list);
			Context::set('project_info',$project_info);
			Context::set('file_info',$file_info);

			// set template_file to be register_file.html
            $this->setTemplateFile('file_register');
        }

        /**
         * @brief display translation file content page
         **/
		function dispTranslationFileContent(){
			$translation_file_srl = Context::get('translation_file_srl');

			$oTranslationModel =  &getModel('translation');

			$source_lang = Context::get('source_lang')?Context::get('source_lang'):$this->module_info->default_lang;
			$target_lang = Context::get('target_lang')?Context::get('target_lang'):"zh-CN";

			$source_contents = $oTranslationModel->getSourceContents($source_lang,$target_lang,$translation_file_srl);

			Context::set('source_contents',$source_contents);
			Context::set('source_lang',$source_lang);
			Context::set('target_lang',$target_lang);

			// set template_file to be register_file.html
            $this->setTemplateFile('file_content');
		}

		function downloadTranslationFile(){
			$translation_file_srl = Context::get('translation_file_srl');
			$oTranslationModel =  &getModel('translation');

			if($translation_file_srl){
				$filepath = $file_info->target_file;
				$test = $oTranslationModel->getFileAllContents($translation_file_srl);
				Context::set('test11',$test);
			}
			Context::set('test11',$test);

			$this->write_txt($test);
			// set template_file to be register_file.html
            $this->setTemplateFile('download');
			
		}

		function write_txt($contents){

			if(!file_exists("test.xml")){

				$fp = fopen("test.xml","wb");

				fclose($fp);}
				$str = file_get_contents('test.xml');
				$fp = fopen("test.xml","wb");
				fwrite($fp,$contents);
				
				fclose($fp);

			}

    }



?>