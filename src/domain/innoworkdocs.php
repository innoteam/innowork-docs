<?php
/*
 *   Copyright (C) 2003-2009 Innoteam
 *
 */

// ----- Initialization -----
//

require_once('innowork/docs/InnoworkDocument.php');
require_once('innowork/docs/InnoworkDocumentDirectory.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/widgets/WuiWidget.php');
require_once('innomatic/wui/widgets/WuiContainerWidget.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/wui/dispatch/WuiEvent.php');
require_once('innomatic/wui/dispatch/WuiEventRawData.php');
require_once('innomatic/wui/dispatch/WuiDispatcher.php');
require_once('innomatic/locale/LocaleCatalog.php');
require_once('innomatic/locale/LocaleCountry.php'); 

    global $gLocale, $gPage_title, $gXml_def, $gPage_status;

require_once('innowork/core/InnoworkCore.php');
$gInnowork_core = InnoworkCore::instance('innoworkcore', 
    InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
    InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
    );

$gLocale = new LocaleCatalog(
    'innowork-docs::main',
    InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
    );

$gWui = Wui::instance('wui');
$gWui->loadWidget( 'xml' );
$gWui->loadWidget( 'innomaticpage' );
$gWui->loadWidget( 'innomatictoolbar' );

$gXml_def = $gPage_status = '';
$gPage_title = $gLocale->getStr( 'innoworkdocs.title' );
$gCore_toolbars = $gInnowork_core->getMainToolBar();
$gToolbars['docs'] = array(
    'documents' => array(
        'label' => $gLocale->getStr( 'documents.toolbar' ),
        'themeimage' => 'view_icon',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( '', array( array(
            'view',
            'default',
            '' ) ) )
        )
    );
/*
$gToolbars['prefs'] = array(
    'prefs' => array(
        'label' => $gLocale->getStr( 'preferences.toolbar' ),
        'themeimage' => 'configure',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( 'innoworkbillingprefs', array( array(
            'view',
            'default',
            '' ) ) )
        )
    );
*/

// ----- Action dispatcher -----
//
$gAction_disp = new WuiDispatcher( 'action' );

$gAction_disp->addEvent(
    'update',
    'action_update'
    );
function action_update(
    $eventData
    )
{
    global $gLocale, $gPage_status;

    if ( $eventData['fileid'] )
    {
        $innowork_doc = new InnoworkDocument(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
            $eventData['fileid']
            );

        $innowork_doc->Edit(
            array(
                'realname' => $eventData['name'],
                'keywords' => $eventData['keywords'],
                'projectid' => isset($eventData['projectid'] ) ? $eventData['projectid'] : '',
                'customerid' => isset($eventData['customerid'] ) ? $eventData['customerid'] : ''
                )
            );

        if ( strlen( $eventData['file']['tmp_name'] ) )
        {
            $innowork_doc_data = $innowork_doc->getItem();

            $innowork_doc->setFile(
                $eventData['file']['tmp_name'],
                $innowork_doc_data['realname']
                );

            unlink( $eventData['file']['tmp_name'] );
        }

        $gPage_status = $gLocale->getStr( 'file_properties_set.status' );
    }
    elseif ( $eventData['directoryid'] )
    {
        $innowork_dir = new InnoworkDocumentDirectory(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
            $eventData['directoryid']
            );

        $innowork_dir->Edit(
            array(
                'directoryname' => $eventData['dirname']
                )
            );

        $gPage_status = $gLocale->getStr( 'dir_properties_set.status' );
    }
}

$gAction_disp->addEvent('editfile', 'action_editfile');
function action_editfile($eventData) {
    global $gLocale, $gPage_status;

    if ( $eventData['id'] )
    {
            $tmp_file = new InnoworkDocument(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                $eventData['id']
                );

            $tmp_file_data = $tmp_file->getItem();

            $tmp_dir = new InnoworkDocumentDirectory(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                $tmp_file_data['directoryid']
                );

            $tmp_dir_data = $tmp_dir->getItem();

        $fname = $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$tmp_file_data['storedname'];

        if ( $fh = fopen( $fname, 'wb' ) )
        {
            fwrite($fh,$eventData['content']);
            fclose( $fh );
            $gPage_status = $gLocale->getStr( 'file_edit.status' );
        }
        else $gPage_status = $gLocale->getStr( 'file_not_edit.status' );
    }
}

$gAction_disp->Dispatch();

// ----- Main dispatcher -----
//
$gMain_disp = new WuiDispatcher( 'view' );

$gMain_disp->addEvent(
    'default',
    'main_default' );
function main_default( $eventData )
{
    global $gLocale, $gPage_title, $gXml_def, $gPage_status;

        $gXml_def =
'    <innoworkdocsview><name>innoworkdocs</name>
      <args>
        <defaultaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( 'innoworkdocs',
                array(
                    array(
                        'view',
                        'default'
                        )
                    )
                )
            ).'</defaultaction>
        <dirupdateaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( 'innoworkdocs',
                array(
                    array(
                        'view',
                        'dirproperties'
                        )
                    )
                )
            ).'</dirupdateaction>
        <docupdateaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( 'innoworkdocs',
                array(
                    array(
                        'view',
                        'docproperties'
                        )
                    )
                )
            ).'</docupdateaction>
        <editfileaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( 'innoworkdocs',
                array(
                    array(
                        'view',
                        'editfile'
                        )
                    )
                )
            ).'</editfileaction>
        <disp>view</disp>
      </args>
    </innoworkdocsview>
';
}

$gMain_disp->addEvent(
    'showdir',
    'main_showdir' );
function main_showdir( $eventData )
{
    global $gLocale, $gPage_title, $gXml_def, $gPage_status;

        $gXml_def =
'    <innoworkdocsview><name>innoworkdocs</name>
      <args>
        <defaultaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( 'innoworkdocs',
                array(
                    array(
                        'view',
                        'default'
                        )
                    )
                )
            ).'</defaultaction>
        <dirupdateaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( 'innoworkdocs',
                array(
                    array(
                        'view',
                        'dirproperties'
                        )
                    )
                )
            ).'</dirupdateaction>
        <docupdateaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( 'innoworkdocs',
                array(
                    array(
                        'view',
                        'docproperties'
                        )
                    )
                )
            ).'</docupdateaction>
        <editfileaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( 'innoworkdocs',
                array(
                    array(
                        'view',
                        'editfile'
                        )
                    )
                )
            ).'</editfileaction>
        <disp>view</disp>
        <directoryid>'.$eventData['id'].'</directoryid>
      </args>
    </innoworkdocsview>
';
}

$gMain_disp->addEvent(
    'docproperties',
    'main_docproperties'
    );
function main_docproperties(
    $eventData
    )
{
    global $gXml_def, $gLocale;

    $country = new LocaleCountry( InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry() );

    if ( !isset($eventData['id'] ) ) $eventData['id'] = '';

        // Available applications
		require_once('innomatic/application/ApplicationDependencies.php');
        $app_dep = new ApplicationDependencies( InnomaticContainer::instance('innomaticcontainer')->getDataAccess() );

        if ( $app_dep->IsEnabled(
            'innowork-groupware',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId() )
            )
        {
            $innowork_company = new InnoworkCompany(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
                );

            $customers_search = $innowork_company->Search(
                '',
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId()
                );

            $directory_available = true;
        }
        else
        {
            $directory_available = false;
        }

        if ( $app_dep->IsEnabled(
            'innowork-groupware',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId() )
            )
        {
            $innowork_project = new InnoworkProject(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
                );

            $projects_search = $innowork_project->Search(
                '',
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId()
                );

            $projects_available = true;
        }
        else
        {
            $projects_available = false;
        }

        if ( $eventData['id'] )
        {
            $tmp_file = new InnoworkDocument(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                $eventData['id']
                );

            $tmp_file_data = $tmp_file->getItem();

            $tmp_dir = new InnoworkDocumentDirectory(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                $tmp_file_data['directoryid']
                );

            $tmp_dir_data = $tmp_dir->getItem();

            if ( $tmp_file_data['directoryid'] ) $headers[0]['label'] = $tmp_dir->getRealPath().$tmp_file_data['realname'];
            else $headers[0]['label'] = '/'.$tmp_file_data['realname'];

            $gXml_def =
'<horizgroup>
  <children>

    <table>
      <args>
        <headers type="array">'.WuiXml::encode( $headers ).'</headers>
      </args>
      <children>

      <horizgroup row="0" col="0">
        <args>
          <align>top</align>
        </args>
        <children>

        <vertgroup>
          <args>
            <align>center</align>
          </args>
          <children>

        <button>
          <args>
            <themeimage>document</themeimage>
            <themeimagetype>mimetypes</themeimagetype>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'download.link' ) ).'</label>
            <horiz>false</horiz>
            <action type="encoded">'.urlencode(
                'innoworkdocs?wui[wui][evd][innoworkdocsaction]=getfile&wui[wui][evd][innoworkdocs-doc-id]='.$eventData['id']
                ).'</action>
          </args>
        </button>

          </children>
        </vertgroup>

      <form><name>properties</name>
        <args>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'action',
                            'update',
                            array(
                                'fileid' => $eventData['id']
                                )
                            )
                        )
                    )
                ).'</action>
        </args>
        <children>

        <grid>
          <children>

                <label row="0" col="0">
                  <args>
                    <label type="encoded">'.urlencode( $gLocale->getStr( 'filename.label' ) ).'</label>
                  </args>
                </label>

                <string row="0" col="1"><name>name</name>
                  <args>
                    <disp>action</disp>
                    <value type="encoded">'.urlencode( $tmp_file_data['realname'] ).'</value>
                    <size>50</size>
                  </args>
                </string>

                <label row="1" col="0">
                  <args>
                    <label type="encoded">'.urlencode( $gLocale->getStr( 'file.label' ) ).'</label>
                  </args>
                </label>

                <file row="1" col="1"><name>file</name>
                  <args>
                    <disp>action</disp>
                    <size>40</size>
                  </args>
                </file>

                <label row="2" col="0">
                  <args>
                    <label type="encoded">'.urlencode( $gLocale->getStr( 'keywords.label' ) ).'</label>
                  </args>
                </label>

                <string row="2" col="1"><name>keywords</name>
                  <args>
                    <disp>action</disp>
                    <size>50</size>
                    <value type="encoded">'.urlencode( $tmp_file_data['keywords'] ).'</value>
                  </args>
                </string>
';

$row = 3;

        if ( $directory_available )
        {
            $customers = array();
            $customers[0] = $gLocale->getStr( 'nocustomer.label' );

            foreach ( $customers_search as $id => $customer )
            {
                $customers[$id] = $customer['companyname'];
            }

            $gXml_def .=
'                <label row="'.$row.'" col="0">
                  <args>
                    <label type="encoded">'.urlencode( $gLocale->getStr( 'customer.label' ) ).'</label>
                  </args>
                </label>

                <combobox row="'.$row++.'" col="1"><name>customerid</name>
                  <args>
                    <disp>action</disp>
                    <elements type="array">'.WuiXml::encode( $customers ).'</elements>
                    <default>'.$tmp_file_data['customerid'].'</default>
                  </args>
                </combobox>';

            unset( $customers );
        }

        if ( $projects_available )
        {
            $projects = array();
            $projects[0] = $gLocale->getStr( 'noproject.label' );

            foreach ( $projects_search as $id => $project )
            {
                $projects[$id] = $project['name'];
            }

            $gXml_def .=
'                <label row="'.$row.'" col="0">
                  <args>
                    <label type="encoded">'.urlencode( $gLocale->getStr( 'project.label' ) ).'</label>
                  </args>
                </label>

                <combobox row="'.$row++.'" col="1"><name>projectid</name>
                  <args>
                    <disp>action</disp>
                    <elements type="array">'.WuiXml::encode( $projects ).'</elements>
                    <default>'.$tmp_file_data['projectid'].'</default>
                  </args>
                </combobox>';

            unset( $projects );
        }

            $gXml_def .=
'                <label row="'.$row.'" col="0">
                  <args>
                    <label type="encoded">'.urlencode( $gLocale->getStr( 'size.label' ) ).'</label>
                  </args>
                </label>

                <string row="'.$row.'" col="1">
                  <args>
                    <readonly>true</readonly>
                    <size>15</size>
                    <value type="encoded">'.urlencode( $country->FormatNumber( $tmp_file_data['size'] ) ).'</value>
                  </args>
                </string>

          </children>
        </grid>

        </children>
        </form>

        </children>
        </horizgroup>

        <horizgroup row="1" col="0">
        <children>

        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <formsubmit>properties</formsubmit>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'apply.button' ) ).'</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'action',
                            'update',
                            array(
                                'fileid' => $eventData['id']
                                )
                            ),
                        array(
                            'wui',
                            'chdir',
                            array(
                                'innoworkdocsaction' => 'chdir',
                                'directoryid' => $tmp_file_data['directoryid']
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>
        <button>
          <args>
            <themeimage>fileclose</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'close.button' ) ).'</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'wui',
                            'chdir',
                            array(
                                'innoworkdocsaction' => 'chdir',
                                'directoryid' => $tmp_file_data['directoryid']
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>
        <button>
          <args>
            <themeimage>edittrash</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'trash.button' ) ).'</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'wui',
                            'removefile',
                            array(
                                'innoworkdocsaction' => 'removefile',
                                'innoworkdocs-doc-id' => $eventData['id']
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

        </children>
        </horizgroup>

      </children>
    </table>

      <innoworkitemacl><name>itemacl</name>
        <args>
          <itemtype>'.InnoworkDocument::ITEM_TYPE.'</itemtype>
          <itemid>'.$eventData['id'].'</itemid>
          <itemownerid>'.$tmp_file_data['ownerid'].'</itemownerid>
          <defaultaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( '',
                array(
                    array(
                        'view',
                        'docproperties',
                        array(
                            'id' => $eventData['id']
                            )
                        )
                    )
                )
            ).'</defaultaction>
        </args>
      </innoworkitemacl>

  </children>
</horizgroup>';
    }
}

$gMain_disp->addEvent(
    'dirproperties',
    'main_dirproperties'
    );
function main_dirproperties(
    $eventData
    )
{
    global $gXml_def, $gLocale;

    if ( !isset($eventData['id'] ) ) $eventData['id'] = '';

        // Available applications

		require_once('innomatic/application/ApplicationDependencies.php');
        $app_dep = new ApplicationDependencies( InnomaticContainer::instance('innomaticcontainer')->getDataAccess() );

        if ( $app_dep->IsEnabled(
            'innowork-groupware',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId() )
            )
        {
            $innowork_company = new InnoworkCompany(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
                );

            $customers_search = $innowork_company->Search(
                '',
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId()
                );

            $directory_available = true;
        }
        else
        {
            $directory_available = false;
        }

        if ( $app_dep->IsEnabled(
            'innowork-groupware',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId() )
            )
        {
            $innowork_project = new InnoworkProject(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
                );

            $projects_search = $innowork_project->Search(
                '',
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId()
                );

            $projects_available = true;
        }
        else
        {
            $projects_available = false;
        }

        if ( $eventData['id'] != 0 )
        {
            $tmp_dir = new InnoworkDocumentDirectory(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                $eventData['id']
                );

            $tmp_dir_data = $tmp_dir->getItem();

            $headers[0]['label'] = $tmp_dir->getRealPath();

        $gXml_def =
'<horizgroup>
  <children>

    <table>
      <args>
        <headers type="array">'.WuiXml::encode( $headers ).'</headers>
      </args>
      <children>

        <form row="0" col="0"><name>properties</name>
          <args>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'action',
                            'update',
                            array(
                                'directoryid' => $eventData['id']
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
          <children>

        <horizgroup>
          <children>

            <label>
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'dirname.label' ) ).'</label>
              </args>
            </label>

            <string><name>dirname</name>
              <args>
                <disp>action</disp>
                <size>50</size>
                <value type="encoded">'.urlencode( $tmp_dir_data['directoryname'] ).'</value>
              </args>
            </string>

          </children>
        </horizgroup>

          </children>
        </form>

        <horizgroup row="1" col="0">
        <children>
        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <formsubmit>properties</formsubmit>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'apply.button' ) ).'</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'action',
                            'update',
                            array(
                                'directoryid' => $eventData['id']
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>
        <button>
          <args>
            <themeimage>fileclose</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'close.button' ) ).'</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>
        <button>
          <args>
            <themeimage>edittrash</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'trash.button' ) ).'</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'wui',
                            'removedir',
                            array(
                                'innoworkdocsaction' => 'removedir',
                                'innoworkdocs-dir-id' => $eventData['id']
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

        </children>
        </horizgroup>

      </children>
    </table>

      <innoworkitemacl><name>itemacl</name>
        <args>
          <itemtype>'.InnoworkDocumentDirectory::ITEM_TYPE.'</itemtype>
          <itemid>'.$eventData['id'].'</itemid>
          <itemownerid>'.$tmp_dir_data['ownerid'].'</itemownerid>
          <defaultaction type="encoded">'.urlencode(
            WuiEventsCall::buildEventsCallString( '',
                array(
                    array(
                        'view',
                        'dirproperties',
                        array(
                            'id' => $eventData['id']
                            )
                        )
                    )
                )
            ).'</defaultaction>
        </args>
      </innoworkitemacl>

  </children>
</horizgroup>';

    }
}

$gMain_disp->addEvent(
    'editfile',
    'main_editfile'
    );
function main_editfile(
    $eventData
    )
{
    global $gXml_def, $gLocale;

        if ( $eventData['id'] )
        {
            $tmp_file = new InnoworkDocument(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                $eventData['id']
                );

            $tmp_file_data = $tmp_file->getItem();

            $tmp_dir = new InnoworkDocumentDirectory(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                $tmp_file_data['directoryid']
                );

            $tmp_dir_data = $tmp_dir->getItem();

        $fname = $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$tmp_file_data['storedname'];

        $file_content = '';
        if ( $fh = fopen( $fname, 'rb' ) )
        {
            $file_content = fread( $fh, filesize( $fname ) );
            fclose( $fh );
        }

            if ( $tmp_file_data['directoryid'] ) $headers[0]['label'] = $tmp_dir->getRealPath().$tmp_file_data['realname'];
            else $headers[0]['label'] = '/'.$tmp_file_data['realname'];

            $gXml_def =
'<horizgroup>
  <children>

    <table>
      <args>
        <headers type="array">'.WuiXml::encode( $headers ).'</headers>
      </args>
      <children>

      <vertgroup row="0" col="0">
        <args>
          <align>top</align>
        </args>
        <children>

      <form><name>edit</name>
        <args>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'action',
                            'editfile',
                            array(
                                'fileid' => $eventData['id']
                                )
                            )
                        )
                    )
                ).'</action>
        </args>
        <children>

        <vertgroup>
          <args>
            <align>center</align>
          </args>
          <children>

            <text>
              <name>content</name>
              <args>
                <disp>action</disp>
                <rows>15</rows>
                <cols>80</cols>
                <value type="encoded">'.urlencode($file_content).'</value>
              </args>
            </text>

          </children>
        </vertgroup>

        </children>
        </form>
        
        </children>
        </vertgroup>
        
        <horizgroup row="1" col="0">
          <children>

        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <formsubmit>edit</formsubmit>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'apply.button' ) ).'</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            ),
                        array(
                            'action',
                            'editfile',
                            array(
                                'id' => $eventData['id']
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>
        <button>
          <args>
            <themeimage>fileclose</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'close.button' ) ).'</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString( '',
                    array(
                        array(
                            'view',
                            'default'
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

        </children>
        </horizgroup>

      </children>
    </table>

  </children>
</horizgroup>';
    }
}

$gMain_disp->Dispatch();

// ----- Rendering -----
//
$gWui->addChild( new WuiInnomaticPage( 'page', array(
    'pagetitle' => $gPage_title,
    'icon' => 'document',
    'menu' => $gInnowork_core->getMainMenu(),
    'toolbars' => array(
        new WuiInnomaticToolBar(
            'core',
            array(
                'toolbars' => $gCore_toolbars
                ) ),
        new WuiInnomaticToolbar(
            'view',
            array(
                'toolbars' => $gToolbars
                ) )
            ),
    'maincontent' => new WuiXml(
        'page', array(
            'definition' => $gXml_def
            ) ),
    'status' => $gPage_status
    ) ) );

$gWui->render();

?>
