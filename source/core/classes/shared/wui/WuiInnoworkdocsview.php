<?php
/*
 *   Copyright (C) 2003-2009 Innoteam
 *
 */

require_once('shared/wui/WuiXml.php');

function _innowork_docs_list_action_builder($pageNumber) {
    return $GLOBALS['innowork-docs-defaction'].
        '&wui[wui][evd][innoworkdocspagenumber]='.$pageNumber;
}

class WuiInnoworkDocsView extends WuiXml {
    var $mDirectoryId;
    var $mFileId;
    var $mDefaultAction;
    var $mDocUpdateAction;
    var $mDirUpdateAction;
    var $mEditFileAction;
    var $mDisp;
    var $mViewBy;
    var $mOrderBy;
    var $mOrderBySortOrder;
    var $mOrderByOrderDirs;
    var $mAdvanced;
    var $mAction = 'showfiles';

    public function __construct(
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
        )
    {
        parent::__construct(
            $elemName,
            $elemArgs,
            $elemTheme,
            $dispEvents
            );

        if ( isset($this->mArgs['action'] ) )
            $this->mAction = $this->mArgs['action'];

        if ( isset($this->mArgs['disp'] ) )
            $this->mDisp = $this->mArgs['disp'];

        if ( isset($this->mArgs['fileid'] ) )
            $this->mFileId = $this->mArgs['fileid'];

        require_once('shared/wui/WuiSessionkey.php');
        
        if ( isset($this->mArgs['directoryid'] ) )
        {
            $this->mDirectoryId = $this->mArgs['directoryid'];
            $docs_dir_sk = new WuiSessionKey( 'innoworkdocs_dir', array(
                    'value' => $this->mDirectoryId
                ) );
        }
        else
        {
            $docs_dir_sk = new WuiSessionKey( 'innoworkdocs_dir' );
            $this->mDirectoryId = $docs_dir_sk->mValue;
            unset( $docs_dir_sk );
        }
        if ( !strlen( $this->mDirectoryId ) ) $this->mDirectoryId = 0;

        if ( isset($this->mArgs['defaultaction'] ) )
            $this->mDefaultAction = $this->mArgs['defaultaction'];

        if ( isset($this->mArgs['docupdateaction'] ) )
            $this->mDocUpdateAction = $this->mArgs['docupdateaction'];

        if ( isset($this->mArgs['dirupdateaction'] ) )
            $this->mDirUpdateAction = $this->mArgs['dirupdateaction'];

        if ( isset($this->mArgs['editfileaction'] ) )
            $this->mEditFileAction = $this->mArgs['editfileaction'];

        $GLOBALS['innowork-docs-defaction'] = $this->mDefaultAction;

        if ( isset($this->mArgs['viewby'] ) ) $this->mViewBy = $this->mArgs['viewby'];
        else
        {
            $docs_viewby_sk = new WuiSessionKey( 'innoworkdocs_viewby' );
            $this->mViewBy = $docs_viewby_sk->mValue;
            unset( $docs_viewby_sk );
        }

        if ( !strlen( $this->mViewBy ) ) $this->mViewBy = 'details';

        if ( isset($this->mArgs['orderby'] ) ) $this->mOrderBy = $this->mArgs['orderby'];
        else
        {
            $docs_orderby_sk = new WuiSessionKey( 'innoworkdocs_orderby' );
            $this->mOrderBy = $docs_orderby_sk->mValue;
            unset( $docs_orderby_sk );
        }

        if ( !strlen( $this->mOrderBy ) ) $this->mOrderBy = 'name';

        if ( isset($this->mArgs['orderbysortorder'] ) ) $this->mOrderBySortOrder = $this->mArgs['orderbysortorder'];
        else
        {
            $docs_orderby_sortorder_sk = new WuiSessionKey( 'innoworkdocs_orderby_sortorder' );
            $this->mOrderBySortOrder = $docs_orderby_sortorder_sk->mValue;
            unset( $docs_viewby_sk );
        }

        if ( !strlen( $this->mOrderBySortOrder ) ) $this->mOrderBySortOrder = 'az';

        if ( isset($this->mArgs['orderbyorderdirs'] ) ) $this->mOrderByOrderDirs = $this->mArgs['orderbyorderdirs'];
        else
        {
            $docs_orderby_orderdirs_sk = new WuiSessionKey( 'innoworkdocs_orderby_orderdirs' );
            $this->mOrderByOrderDirs = $docs_orderby_orderdirs_sk->mValue;
            unset( $docs_orderby_orderdirs_sk );
        }

        if ( isset($this->mArgs['advanced'] ) ) $this->mAdvanced = $this->mArgs['advanced'];
        else
        {
            $advanced_sk = new WuiSessionKey( 'innoworkdocs_advanced' );
            $this->mAdvanced = $advanced_sk->mValue;
            unset( $advanced_sk );
        }

        if ( !strlen( $this->mAdvanced ) ) $this->mAdvanced = 'false';

        if ( !strlen( $this->mOrderByOrderDirs ) ) $this->mOrderByOrderDirs = 'dirsfirst';

        $this->_FillDefinition();
    }

    /*!
     @function _FillDefinition
     */
    function _FillDefinition()
    {
        $result = false;
        
        require_once('mimetypes/MimeTypes.php');
        $mime = new MimeTypes();

        require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php'); 
        $locale = new LocaleCatalog(
            'innowork-docs::misc',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getLanguage()
            );

        // Available applications

    $core = InnoworkCore::instance('innoworkcore', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess());
    $summ = $core->getSummaries();

    if (isset($summ['directorycompany']))
        {
            $innowork_company = new InnoworkCompany(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
                );

            $customers_search = $innowork_company->Search(
                '',
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
                );

            $directory_available = true;
        }
        else
        {
            $directory_available = false;
        }

    if (isset($summ['project']))
        {
            $innowork_project = new InnoworkProject(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
                );

            $projects_search = $innowork_project->Search(
                array(
                    'done' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse
                ),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
                );

            $projects_available = true;
        }
        else
        {
            $projects_available = false;
        }

        // Directory

        //$headers[0]['label'] = '/';

        if ( $this->mDirectoryId != 0 )
        {
            $tmp_dir = new InnoworkDocumentDirectory(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                $this->mDirectoryId
                );

            $tmp_dir_data = $tmp_dir->getItem();

            $headers[0]['label'] = $tmp_dir->getRealPath();
        }
        else $headers[0]['label'] = '/';

        // Files

        $innowork_dirs = new InnoworkDocumentDirectory(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
            );
        $dirs_search = $innowork_dirs->Search(
            array(
                'parentid' => $this->mDirectoryId
                ),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
            );

        $innowork_docs = new InnoworkDocument(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
            );
        $docs_search = $innowork_docs->Search(
            array(
                'directoryid' => $this->mDirectoryId
                ),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
            );

        require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php'); 

        $country = new LocaleCountry(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
            );

        $files_array = array();

        foreach ( $dirs_search as $id => $dir )
        {
            $files_array[] = array(
                'type' => 'd',
                'name' => $dir['directoryname'],
                'size' => '',
                'id' => $id,
                'class' => 'directory',
                'projectid' => '',
                'customerid' => ''
                );
        }

        foreach ( $docs_search as $id => $file )
        {
            $files_array[] = array(
                'type' => 'f',
                'name' => $file['realname'],
                'size' => $country->FormatNumber( $file['size'] ),
                'id' => $id,
                'class' => '',
                'projectid' => $file['projectid'],
                'customerid' => $file['customerid']
                );
        }

/*
        if ( $this->mDirectoryId != 0 )
        {
            $files_array[] = array(
                'type' => 'd',
                'name' => '..',
                'size' => 0,
                'id' => 0,
                'projectid' => '',
                'customerid' => ''
                );
        }
*/

        usort( $files_array, array( $this, '_Sort' ) );

        // View mode

        $viewby_array = array(
            'list' => $locale->getStr( 'viewby_list.label' ),
            'details' => $locale->getStr( 'viewby_details.label' ),
            'icons' => $locale->getStr( 'viewby_icons.label' )

            );
            // 'previews' => $locale->getStr( 'viewby_previews.label' )

        // Order mode

        $orderby_array = array(
            'name' => $locale->getStr( 'orderby_name.label' ),
            'size' => $locale->getStr( 'orderby_size.label' ),
            'date' => $locale->getStr( 'orderby_date.label' ),
            'customer' => $locale->getStr( 'orderby_customer.label' ),
            'project' => $locale->getStr( 'orderby_project.label' )
            );

        $sortorder_array = array(
            'az' => $locale->getStr( 'orderby_az_mode.label' ),
            'za' => $locale->getStr( 'orderby_za_mode.label' )
            );

        $dirsviewmode_array = array(
            'dirsfirst' => $locale->getStr( 'orderby_dirsfirst_dir.label' ),
            'filesfirst' => $locale->getStr( 'orderby_filesfirst_dir.label' ),
            'mixed' => $locale->getStr( 'orderby_mixed_dir.label' )
            );

        // Header

        $this->mDefinition =
'<table>
  <args>
    <headers type="array">'.WuiXml::encode( $headers ).'</headers>
  </args>
  <children>

    <vertgroup row="0" col="0">
      <children>

    <horizgroup>
      <children>

        <horizgroup>
          <children>

            <form><name>gohome'.$this->mName.'</name>
              <children>

                <formarg><name>directoryid</name>
                  <args>
                    <disp>wui</disp>
                    <value>0</value>
                  </args>
                </formarg>
                <formarg><name>innoworkdocsaction</name>
                  <args>
                    <disp>wui</disp>
                    <value>chdir</value>
                  </args>
                </formarg>

              </children>
            </form>

            <button>
              <args>
                <themeimage>gohome</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>gohome'.$this->mName.'</formsubmit>
                <action type="encoded">'.urlencode( $this->mDefaultAction ).'</action>
              </args>
            </button>';

        if ( $this->mDirectoryId != 0 )
        {
            $this->mDefinition .=
'            <form><name>goparent'.$this->mName.'</name>
              <children>

                <formarg><name>directoryid</name>
                  <args>
                    <disp>wui</disp>
                    <value>'.$tmp_dir_data['parentid'].'</value>
                  </args>
                </formarg>
                <formarg><name>innoworkdocsaction</name>
                  <args>
                    <disp>wui</disp>
                    <value>chdir</value>
                  </args>
                </formarg>

              </children>
            </form>

            <button>
              <args>
                <themeimage>up</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>goparent'.$this->mName.'</formsubmit>
                <action type="encoded">'.urlencode( $this->mDefaultAction ).'</action>
              </args>
            </button>';
        }

		require_once('innomatic/datatransfer/Clipboard.php');

        $clip = new ClipBoard(
            Clipboard::TYPE_ARRAY,
            '',
            0,
            'innowork-docs',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName()
            );

        if ( $clip->IsValid() )
        {
            $this->mDefinition .=
'            <form><name>paste'.$this->mName.'</name>
              <children>

                <formarg><name>directoryid</name>
                  <args>
                    <disp>wui</disp>
                    <value>'.$tmp_dir_data['id'].'</value>
                  </args>
                </formarg>
                <formarg><name>innoworkdocsaction</name>
                  <args>
                    <disp>wui</disp>
                    <value>paste</value>
                  </args>
                </formarg>

              </children>
            </form>

            <button>
              <args>
                <themeimage>editpaste</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>paste'.$this->mName.'</formsubmit>
                <action type="encoded">'.urlencode( $this->mDefaultAction ).'</action>
              </args>
            </button>';
        }

        $this->mDefinition .=
'          </children>
        </horizgroup>

        <horizgroup>
          <children>

            <form><name>newdir'.$this->mName.'</name>
              <children>

                <string><name>dirname</name>
                  <args>
                    <disp>wui</disp>
                    <size>15</size>
                  </args>
                </string>

                <formarg><name>parentid</name>
                  <args>
                    <disp>wui</disp>
                    <value>'.$this->mDirectoryId.'</value>
                  </args>
                </formarg>
                <formarg><name>innoworkdocsaction</name>
                  <args>
                    <disp>wui</disp>
                    <value>mkdir</value>
                  </args>
                </formarg>

              </children>
            </form>

            <button>
              <args>
                <themeimage>folder_new</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>newdir'.$this->mName.'</formsubmit>
                <action type="encoded">'.urlencode( $this->mDefaultAction ).'</action>
              </args>
            </button>

          </children>
        </horizgroup>

        <horizgroup>
          <children>

            <form><name>viewmode'.$this->mName.'</name>
              <children>

                <formarg><name>innoworkdocsaction</name>
                  <args>
                    <disp>wui</disp>
                    <value>viewoptions</value>
                  </args>
                </formarg>

                <horizgroup>
                  <args>
                    <align>middle</align>
                  </args>
                  <children>

            <label>
              <args>
                <label type="encoded">'.urlencode( $locale->getStr( 'viewby.label' ) ).'</label>
              </args>
            </label>

            <combobox><name>viewby</name>
              <args>
                <disp>wui</disp>
                <elements type="array">'.WuiXml::encode( $viewby_array ).'</elements>
                <default>'.$this->mViewBy.'</default>
              </args>
            </combobox>

            <label>
              <args>
                <label type="encoded">'.urlencode( $locale->getStr( 'orderby.label' ) ).'</label>
              </args>
            </label>

            <combobox><name>orderby</name>
              <args>
                <disp>wui</disp>
                <elements type="array">'.WuiXml::encode( $orderby_array ).'</elements>
                <default>'.$this->mOrderBy.'</default>
              </args>
            </combobox>

            <combobox><name>sortorder</name>
              <args>
                <disp>wui</disp>
                <elements type="array">'.WuiXml::encode( $sortorder_array ).'</elements>
                <default>'.$this->mOrderBySortOrder.'</default>
              </args>
            </combobox>

            <combobox><name>orderdirs</name>
              <args>
                <disp>wui</disp>
                <elements type="array">'.WuiXml::encode( $dirsviewmode_array ).'</elements>
                <default>'.$this->mOrderByOrderDirs.'</default>
              </args>
            </combobox>

            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>viewmode'.$this->mName.'</formsubmit>
                <action type="encoded">'.urlencode( $this->mDefaultAction ).'</action>
              </args>
            </button>

                  </children>
                </horizgroup>

              </children>
            </form>

          </children>
        </horizgroup>

      </children>
    </horizgroup>

      </children>
    </vertgroup>';

        // Files list

        unset( $headers );

        switch ( $this->mViewBy )
        {
        case 'list':
            $headers[1]['label'] = $locale->getStr( 'name.header' );
            $table_rows = 15;
            $table_cols = 1;
            break;

        case 'details':
            $num_headers = 4;
            $headers[2]['label'] = $locale->getStr( 'name.header' );
            $headers[3]['label'] = $locale->getStr( 'size.header' );
            $headers[4]['label'] = $locale->getStr( 'type.header' );
            //$headers[5]['label'] = $locale->getStr( 'date.header' );

            if ( $directory_available ) $headers[++$num_headers]['label'] = $locale->getStr( 'customer.header' );
            if ( $projects_available ) $headers[++$num_headers]['label'] = $locale->getStr( 'project.header' );

            $table_rows = 10;
            $table_cols = 1;
            break;

        case 'icons':
            $table_rows = 5;
            $table_cols = 5;

            $num_rows = floor( count( $files_array ) / $table_cols );

            break;

        case 'previews':
            $table_rows = 5;
            $table_cols = 5;
            break;
        }

        $num_files = count( $files_array );

        $this->mDefinition .=
'<table row="1" col="0"><name>innoworkdocsfiles'.$this->mName.'</name>
  <args>
    <headers type="array">'.WuiXml::encode( $headers ).'</headers>
    <rowsperpage>'.$table_rows.'</rowsperpage>
    <pagesactionfunction>_innowork_docs_list_action_builder</pagesactionfunction>
    <pagenumber>'.( isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocspagenumber'] ) ? Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocspagenumber'] : '' ).'</pagenumber>
    <sessionobjectusername>'.$this->mDirectoryId.'</sessionobjectusername>
    <rows>'.$num_files.'</rows>
    <width>100%</width>
  </args>
  <children>';

        $files_row = 0;
        $files_col = 0;

    $page = 1;

                    if ( isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocspagenumber'] ) )
                    {
                        $page = Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocspagenumber'];
                    }
                    else
                    {
						require_once('shared/wui/WuiTable.php');

                        $table = new WuiTable(
                            'innoworkdocsfiles'.$this->mName,
                            array(
                                'sessionobjectusername' => $this->mDirectoryId
                                )
                            );

                        $page = $table->mPageNumber;
                    }

                    if ( $page > ceil( $num_files / $table_rows ) ) $page = ceil( $num_files / $table_rows );

                    $from = ( $page * $table_rows ) - $table_rows;
                    $to = $from + $table_rows - 1;

        foreach ( $files_array as $file )
        {
            switch ( $this->mViewBy )
            {
            // list
            case 'list':
            if ( $files_row >= $from and $files_row <= $to )
            {

                switch ( $file['type'] )
                {
                case 'd':
                    if ( $file['id'] ) $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=chdir&wui[wui][evd][directoryid]='.$file['id'];
                    else
                    {
                        $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=chdir&wui[wui][evd][directoryid]='.$tmp_dir_data['parentid'];
                    }
                    break;

                case 'f':
                    $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=getfile&wui[wui][evd][innoworkdocs-doc-id]='.$file['id'];
                    break;
                }

                $this->mDefinition .=
'<button row="'.$files_row.'" col="0" halign="" valign="" nowrap="" width="0%">
  <args>
    <themeimage>'.( $file['type'] == 'd' ? ( $file['id'] ? 'folder' : 'link' ) : 'document' ).'</themeimage>
    <themeimagetype>mini</themeimagetype>
    <action type="encoded">'.urlencode( $action ).'</action>
  </args>
</button>
<link row="'.$files_row.'" col="1" halign="" valign="middle" nowrap="true" width="100%">
  <args>
    <label type="encoded">'.urlencode( $file['name'] ).'</label>
    <link type="encoded">'.urlencode( $action ).'</link>
  </args>
</link>';
            }
                $files_row++;

                break;

            // details
            case 'details':
            if ( $files_row >= $from and $files_row <= $to )
            {

                $private = true;

                switch ( $file['type'] )
                {
                case 'd':
                    if ( $file['id'] )
                    {
                        $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=chdir&wui[wui][evd][directoryid]='.$file['id'];

                        $innowork_item = new InnoworkDocumentDirectory(
                            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                            $file['id']
                            );

                        if ( $innowork_item->mAcl->getType() != InnoworkAcl::TYPE_PRIVATE ) $private = false;

                        $prop_action = $this->mDirUpdateAction.
                            '&wui['.$this->mDisp.'][evd][id]='.$file['id'];
                    }
                    else
                    {
                        $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=chdir&wui[wui][evd][directoryid]='.$tmp_dir_data['parentid'];
                    }
                    break;

                case 'f':
                    $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=getfile&wui[wui][evd][innoworkdocs-doc-id]='.$file['id'];

                    $innowork_item = new InnoworkDocument(
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                        $file['id']
                        );

                    if ( $innowork_item->mAcl->getType() != InnoworkAcl::TYPE_PRIVATE ) $private = false;

                    $file['class'] = $innowork_item->getFileType();

                    $prop_action = $this->mDocUpdateAction.
                        '&wui['.$this->mDisp.'][evd][id]='.$file['id'];

                    break;
                }

                $this->mDefinition .=
'<button row="'.$files_row.'" col="0" halign="" valign="" nowrap="" width="0%">
  <args>
    <themeimage>'.( $file['type'] == 'd' ? ( $file['id'] ? 'folder' : 'link' ) : 'document' ).'</themeimage>
    <themeimagetype>mini</themeimagetype>
    <action type="encoded">'.urlencode( $action ).'</action>
  </args>
</button>';

                if ( !$private ) $this->mDefinition .=
'<button row="'.$files_row.'" col="1" halign="" valign="" nowrap="" width="0%">
  <args>
    <themeimage>kuser</themeimage>
    <themeimagetype>mini</themeimagetype>
    <action type="encoded">'.urlencode( $prop_action ).'</action>
  </args>
</button>';
                elseif ( $file['id'] ) $this->mDefinition .=
'<button row="'.$files_row.'" col="1" halign="" valign="" nowrap="" width="0%">
  <args>
    <themeimage>personal</themeimage>
    <themeimagetype>mini</themeimagetype>
    <action type="encoded">'.urlencode( $prop_action ).'</action>
  </args>
</button>';

                $this->mDefinition .=
'<link row="'.$files_row.'" col="2" halign="" valign="middle" nowrap="true" width="100%">
  <args>
    <label type="encoded">'.urlencode( $file['name'] ).'</label>
    <link type="encoded">'.urlencode( $action ).'</link>
  </args>
</link>
<label row="'.$files_row.'" col="3" halign="right" valign="middle" nowrap="true" width="0%">
  <args>
    <label type="encoded">'.urlencode( $file['id'] ? $file['size'] : '' ).'</label>
  </args>
</label>
<label row="'.$files_row.'" col="4" halign="" valign="middle" nowrap="true" width="0%">
  <args>
    <label type="encoded">'.urlencode( $file['class'] ).'</label>
  </args>
</label>
<!--<label row="'.$files_row.'" col="5" halign="" valign="middle" nowrap="true" width="0%">
  <args>
    <label type="encoded">'.urlencode( $file['date'] ).'</label>
  </args>
</label>-->';

                $tmp_col = 5;

                if ( $directory_available )
                {
                    require_once('innowork/groupware/InnoworkCompany.php');

                    $innowork_customer = new InnoworkCompany(
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                        $file['customerid']
                        );

                    $innowork_customer_data = $innowork_customer->getItem();

                    $this->mDefinition .=
'<link row="'.$files_row.'" col="'.$tmp_col++.'" halign="" valign="middle" nowrap="true" width="0%">
  <args>
    <label type="encoded">'.urlencode( $innowork_customer_data['companyname'] ).'</label>
    <link type="encoded">'.urlencode(
        WuiEventsCall::buildEventsCallString(
            'innoworkdirectory',
            array(
                array(
                    'view',
                    'showcompany',
                    array(
                        'id' => $innowork_customer_data['id']
                        )
                    )
                )
            )
        ).'</link>
  </args>
</link>';
                    unset( $innowork_customer );
                    unset( $innowork_customer_data );
                }

                if ( $projects_available )
                {
                    require_once('innowork/groupware/InnoworkProject.php');

                    $innowork_project = new InnoworkProject(
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                        $file['projectid']
                        );

                    $innowork_project_data = $innowork_project->getItem();

                    $this->mDefinition .=
'<link row="'.$files_row.'" col="'.$tmp_col++.'" halign="" valign="middle" nowrap="true" width="0%">
  <args>
    <label type="encoded">'.urlencode( $innowork_project_data['name'] ).'</label>
    <link type="encoded">'.urlencode(
        WuiEventsCall::buildEventsCallString(
            'innoworkprojects',
            array(
                array(
                    'view',
                    'showproject',
                    array(
                        'id' => $innowork_project_data['id']
                        )
                    )
                )
            )
        ).'</link>
  </args>
</link>';
                    unset( $innowork_project );
                    unset( $innowork_project_data );
                }

                if (
                    $file['id']
                    )
                {
                $toolbars = array();

                $toolbars['view']['properties'] = array(
                    'label' => $locale->getStr( 'properties.button' ),
                    'themeimage' => 'zoom',
                    'themeimagetype' => 'mini',
                    'compact' => 'true',
                    'horiz' => 'true',
                    'action' => $prop_action
                    );

                    /*
                if ( $file['type'] == 'f' )
                {
                $toolbars['view']['update'] = array(
                    'label' => $locale->getStr( 'update.button' ),
                    'themeimage' => 'pencil',
                    'horiz' => 'true',
                    'action' => $this->mUpdateAction.'&wui['.$this->mDisp.'][evd][fileid]='.
                    );
                }
                */

                if ( $file['type'] == 'f' )
                {
                    $toolbars['view']['cut'] = array(
                        'label' => $locale->getStr( 'cut.button' ),
                        'themeimage' => 'editcut',
                        'themeimagetype' => 'mini',
                        'compact' => 'true',
                        'horiz' => 'true',
                        'action' => $this->mDefaultAction.
                            '&wui[wui][evd][innoworkdocsaction]=cutfile&wui[wui][evd][innoworkdocs-doc-id]='.$file['id']
                        );
                    $toolbars['view']['remove'] = array(
                        'label' => $locale->getStr( 'remove.button' ),
                        'themeimage' => 'trash',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'needconfirm' => 'true',
                        'compact' => 'true',
                        'confirmmessage' => $locale->getStr( 'removefile.confirm' ),
                        'action' => $this->mDefaultAction.
                            '&wui[wui][evd][innoworkdocsaction]=removefile&wui[wui][evd][innoworkdocs-doc-id]='.$file['id']
                        );
                    if ($file['class'] == 'text/plain') {
                    $toolbars['view']['edit'] = array(
                        'label' => $locale->getStr( 'edit.button' ),
                        'themeimage' => 'pencil',
                        'themeimagetype' => 'mini',
                        'compact' => 'true',
                        'horiz' => 'true',
                        'action' => $this->mEditFileAction.
                            '&wui[main][evd][id]='.$file['id']
                        );
                    }
                }
                elseif ( $file['type'] == 'd' )
                {
                    $toolbars['view']['cut'] = array(
                        'label' => $locale->getStr( 'cut.button' ),
                        'themeimage' => 'editcut',
                        'themeimagetype' => 'mini',
                        'compact' => 'true',
                        'horiz' => 'true',
                        'action' => $this->mDefaultAction.
                            '&wui[wui][evd][innoworkdocsaction]=cutdir&wui[wui][evd][innoworkdocs-dir-id]='.$file['id']
                        );
                    $toolbars['view']['remove'] = array(
                        'label' => $locale->getStr( 'remove.button' ),
                        'themeimage' => 'trash',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'compact' => 'true',
                        'needconfirm' => 'true',
                        'confirmmessage' => $locale->getStr( 'removedir.confirm' ),
                        'action' => $this->mDefaultAction.
                            '&wui[wui][evd][innoworkdocsaction]=removedir&wui[wui][evd][innoworkdocs-dir-id]='.$file['id']
                        );
                }

                $this->mDefinition .=
'<innomatictoolbar row="'.$files_row.'" col="'.$tmp_col.'" valign="middle" nowrap="" width="0%"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode( $toolbars ).'</toolbars>
  </args>
</innomatictoolbar>';
                }
            }

                $files_row++;

                break;

            case 'icons':

                switch ( $file['type'] )
                {
                case 'd':
                    if ( $file['id'] ) $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=chdir&wui[wui][evd][directoryid]='.$file['id'];
                    else
                    {
                        $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=chdir&wui[wui][evd][directoryid]='.$tmp_dir_data['parentid'];
                    }
                    break;

                case 'f':
                    $action = $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=getfile&wui[wui][evd][innoworkdocs-doc-id]='.$file['id'];
                    break;
                }

                $this->mDefinition .=
'<button row="'.$files_row.'" col="'.$files_col.'" halign="center" valign="top" nowrap="false" width="20%">
  <args>
    <frame>false</frame>
    <themeimage>'.( $file['type'] == 'd' ? ( $file['id'] ? 'folder' : 'link' ) : 'document' ).'</themeimage>
    <themeimagetype>'.( $file['type'] == 'd' ? 'filesystems' : 'mimetypes' ).'</themeimagetype>
    <label type="encoded">'.urlencode( $file['name'] ).'</label>
    <action type="encoded">'.urlencode( $action ).'</action>
  </args>
</button>';

                $files_col++;

                if ( $files_col == $table_cols )
                {
                    $files_col = 0;
                    $files_row++;
                }

                break;

            case 'previews':
                break;
            }
        }


        if (
            (
                $this->mViewBy == 'icons'
                or
                $this->mViewBy == 'previews'
            )
            and
            (
                $files_col > 0
            )
            )
        {
            while ( !( $files_col == $table_cols ) )
            {
                $this->mDefinition .= '<empty row="'.$files_row.'" col="'.$files_col.'" halign="" valign="" width="20%"/>';

                $files_col++;
            }
        }

        $this->mDefinition .=
'  </children>
</table>';

        // Footer

        $this->mDefinition .=
'    <vertgroup row="2" col="0">
  <args>
    <width>100%</width>
  </args>
      <children>

            <form><name>newfile'.$this->mName.'</name>
              <args>
                <action type="encoded">'.urlencode( $this->mDefaultAction ).'</action>
              </args>
              <children>

              <vertgroup>
                <children>

                <horizgroup>
                  <args>
                    <align>middle</align>
                  </args>
                  <children>

                <label>
                  <args>
                    <label type="encoded">'.urlencode( $locale->getStr( 'file.label' ) ).'</label>
                  </args>
                </label>

                <file><name>newfile</name>
                  <args>
                    <disp>wui</disp>
                    <size>20</size>
                  </args>
                </file>

                <label>
                  <args>
                    <label type="encoded">'.urlencode( $locale->getStr( 'keywords.label' ) ).'</label>
                  </args>
                </label>

                <string><name>keywords</name>
                  <args>
                    <disp>wui</disp>
                    <size>20</size>
                  </args>
                </string>

                <formarg><name>directoryid</name>
                  <args>
                    <disp>wui</disp>
                    <value>'.$this->mDirectoryId.'</value>
                  </args>
                </formarg>
                <formarg><name>innoworkdocsaction</name>
                  <args>
                    <disp>wui</disp>
                    <value>addfile</value>
                  </args>
                </formarg>

                  </children>
                </horizgroup>

                <horizgroup>
                  <args>
                    <align>middle</align>
                  </args>
                  <children>';

        if ( $directory_available )
        {
            $customers = array();
            $customers[0] = $locale->getStr( 'nocustomer.label' );

            foreach ( $customers_search as $id => $customer )
            {
                $customers[$id] = $customer['companyname'];
            }

            $this->mDefinition .=
'                <combobox><name>customerid</name>
                  <args>
                    <disp>wui</disp>
                    <elements type="array">'.WuiXml::encode( $customers ).'</elements>
                  </args>
                </combobox>';

            unset( $customers );
        }

        if ( $projects_available )
        {
            $projects = array();
            $projects[0] = $locale->getStr( 'noproject.label' );

            foreach ( $projects_search as $id => $project )
            {
                $projects[$id] = $project['name'];
            }

            $this->mDefinition .=
'                <combobox><name>projectid</name>
                  <args>
                    <disp>wui</disp>
                    <elements type="array">'.WuiXml::encode( $projects ).'</elements>
                  </args>
                </combobox>';

            unset( $projects );
        }

        $this->mDefinition .=
'                  </children>
                </horizgroup>

                </children>
                </vertgroup>

              </children>
            </form>

            <horizbar/>

            <horizgroup>
              <children>

            <button>
              <args>
                <themeimage>filenew</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>newfile'.$this->mName.'</formsubmit>
                <label type="encoded">'.urlencode( $locale->getStr( 'newfile.button' ) ).'</label>
                <action type="encoded">'.urlencode( $this->mDefaultAction ).'</action>
              </args>
            </button>';

        if ( $this->mAdvanced == 'true' )
        {
            $this->mDefinition .=
'            <button>
              <args>
                <themeimage>filenew</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>newfile'.$this->mName.'</formsubmit>
                <label type="encoded">'.urlencode( $locale->getStr( 'newdirasfile.button' ) ).'</label>
                <action type="encoded">'.urlencode( $this->mDefaultAction.'&wui[wui][evd][innoworkdirasfile]=true' ).'</action>
              </args>
            </button>
            <button>
              <args>
                <themeimage>edit_remove</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <label type="encoded">'.urlencode( $locale->getStr( 'setsimple.button' ) ).'</label>
                <action type="encoded">'.urlencode( $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=setsimple' ).'</action>
              </args>
            </button>';
        }
        else
        {
            $this->mDefinition .=
'            <button>
              <args>
                <themeimage>edit_add</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <label type="encoded">'.urlencode( $locale->getStr( 'setadvanced.button' ) ).'</label>
                <action type="encoded">'.urlencode( $this->mDefaultAction.'&wui[wui][evd][innoworkdocsaction]=setadvanced' ).'</action>
              </args>
            </button>';
        }

        $this->mDefinition .=
'              </children>
            </horizgroup>

      </children>
    </vertgroup>


  </children>
</table>';

        $result = true;

        return $result;
    }

    function _Sort( $a, $b )
    {
        $result = 0;

        if (
            $a['type'] == $b['type']
            or
            $this->mOrderByOrderDirs == 'mixed'
            )
        {
            $res = strcmp( $a[$this->mOrderBy], $b[$this->mOrderBy] );

            switch ( $res )
            {
            case -1:
                $result = $this->mOrderBySortOrder == 'az' ? -1 : 1;
                break;

            case 0:
                $result = 0;
                break;

            case 1:
                $result = $this->mOrderBySortOrder == 'az' ? 1 : -1;
                break;
            }
        }
        else
        {
            if ( $a['type'] == 'd' )
            {
                $result = $this->mOrderByOrderDirs == 'dirsfirst' ? -1 : 1;
            }
            else
            {
                $result = $this->mOrderByOrderDirs == 'dirsfirst' ? 1 : -1;
            }
        }

        return $result;
    }
}

?>
