<?php
require_once('innowork/core/InnoworkItem.php');

class InnoworkDocument extends InnoworkItem
{
    var $mTable = 'innowork_docs';
    var $mNewDispatcher = 'view';
    var $mNewEvent = 'newdocument';
    var $mNoTrash = false;
    var $mConvertible = true;
    const ITEM_TYPE = 'document';

    function InnoworkDocument(
        $rrootDb,
        $rdomainDA,
        $documentId = 0
        )
    {
        parent::__construct(
            $rrootDb,
            $rdomainDA,
            InnoworkDocument::ITEM_TYPE,
            $documentId
            );

        $this->mKeys['directoryid'] = 'table:innowork_docs_dirs:name:integer';
        $this->mKeys['customerid'] = 'table:innowork_directory_companies:companyname:integer';
		
        require_once('innomatic/application/ApplicationDependencies.php');
        $app_dep = new ApplicationDependencies( InnomaticContainer::instance('innomaticcontainer')->getDataAccess() );

        if ($app_dep->IsEnabled(
            'innowork-groupware',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId())) {
        	$this->mKeys['projectid'] = 'table:innowork_projects:name:integer';
        	$this->mSearchResultKeys[] = 'projectid';
        	$this->mViewableSearchResultKeys[] = 'projectid';
        	$this->mGenericFields['projectid'] = 'projectid';
        }
        $this->mKeys['realname'] = 'text';
        //$this->mKeys['description'] = 'text';
        $this->mKeys['keywords'] = 'text';
        $this->mKeys['size'] = 'integer';

        $this->mSearchResultKeys[] = 'realname';
        $this->mSearchResultKeys[] = 'customerid';
        //$this->mSearchResultKeys[] = 'description';
        $this->mSearchResultKeys[] = 'size';

        $this->mViewableSearchResultKeys[] = 'realname';
        $this->mViewableSearchResultKeys[] = 'customerid';
        //$this->mViewableSearchResultKeys[] = 'description';

        $this->mSearchOrderBy = 'realname';
        $this->mShowDispatcher = 'view';
        $this->mShowEvent = 'docproperties';

        $this->mGenericFields['companyid'] = 'customerid';
        $this->mGenericFields['title'] = 'realname';
        $this->mGenericFields['content'] = 'content';
        $this->mGenericFields['binarycontent'] = 'binarycontent';
    }

    function doCreate(
        $params,
        $userId
        )
    {
        $result = false;

        if (
            !isset($params['projectid'] )
            or !strlen( $params['projectid'] )
            ) $params['projectid'] = '0';

        if (
            !isset($params['customerid'] )
            or !strlen( $params['customerid'] )
            ) $params['customerid'] = '0';

        if (
            !isset($params['directoryid'] )
            or !strlen( $params['directoryid'] )
            ) $params['directoryid'] = '0';

        if ( count( $params ) )
        {
            

            $item_id = $this->mrDomainDA->getNextSequenceValue( $this->mTable.'_id_seq' );

            $key_pre = $value_pre = $keys = $values = '';

            while ( list( $key, $val ) = each( $params ) )
            {
                $key_pre = ',';
                $value_pre = ',';

                switch ( $key )
                {
                case 'realname':
                case 'description':
                case 'keywords':
                case 'storedname':
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                    break;

                case 'customerid':
                case 'projectid':
                case 'directoryid':
                case 'size':
                    if ( !strlen( $key ) ) $key = 0;
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$val;
                    break;

                default:
                    break;
                }
            }

            if ( strlen( $values ) )
            {
                if ( $this->mrDomainDA->Execute( 'INSERT INTO '.$this->mTable.' '.
                                               '(id,ownerid'.$keys.') '.
                                               'VALUES ('.$item_id.','.
                                               $userId.
                                               $values.')' ) )
                {
                    $result = $item_id;
                }
            }
        }

        return $result;
    }

    function doEdit(
        $params
        )
    {
        $result = false;

        if ( $this->mItemId )
        {
            if ( count( $params ) )
            {
                $start = 1;
                $update_str = '';

                if (
                    isset($params['realname'] )
                    and !strlen( $params['realname'] )
                    ) unset( $params['realname'] );

                while ( list( $field, $value ) = each( $params ) )
                {
                    if ( $field != 'id' )
                    {
                        switch ( $field )
                        {
                        case 'realname':
                        case 'description':
                        case 'keywords':
                        case 'storedname':
                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            $start = 0;
                            break;

                        case 'customerid':
                        case 'projectid':
                        case 'directoryid':
                        case 'size':
                            if ( !strlen( $value ) ) $value = 0;
                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$value;
                            $start = 0;
                            break;

                        default:
                            break;
                        }
                    }
                }

                $query = &$this->mrDomainDA->Execute(
                    'UPDATE '.$this->mTable.' '.
                    'SET '.$update_str.' '.
                    'WHERE id='.$this->mItemId );

                if ( $query )
                {
                    if (
                        isset($params['realname'] )
                        and strlen( $params['realname'] )
                        )
                    {
                        $data = $this->getItem();

                        $tmp_dir = new InnoworkDocumentDirectory(
                            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                            $data['directoryid']
                            );

                        if ( $fh = fopen(
                                $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$data['storedname'].'.info',
                                'w' ) )
                        {
                            fwrite( $fh, $params['realname'] );
                            fclose( $fh );
                        }
                    }

                    $result = true;
                }
            }
        }

        return $result;
    }

    function doRemove(
        $userId
        )
    {
        $data = $this->getItem();

        $tmp_dir = new InnoworkDocumentDirectory(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
            $data['directoryid']
            );

        unlink( $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$data['storedname'] );
        unlink( $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$data['storedname'].'.info' );

        $result = $this->mrDomainDA->Execute(
            'DELETE FROM '.$this->mTable.' '.
            'WHERE id='.$this->mItemId
            );

        return $result;
    }

    function doGetItem(
        $userId
        )
    {
        $result = FALSE;

        $item_query = &$this->mrDomainDA->Execute(
            'SELECT * '.
            'FROM '.$this->mTable.' '.
            'WHERE id='.$this->mItemId
            );

        if (
            is_object( $item_query )
            and $item_query->getNumberRows()
            )
        {
            $result = $item_query->getFields();
        }

        return $result;
    }

    function setFile(
        $filePath
        )
    {
        $result = false;

        if ( $this->mItemId )
        {
            $data = $this->getItem();

            $tmp_dir = new InnoworkDocumentDirectory(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                $data['directoryid']
                );

            if ( strlen( $data['storedname'] ) ) $stored_name = $data['storedname'];
            else $stored_name = md5( microtime() );

            $dest_name = $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$stored_name;

            $result = copy(
                $filePath,
                $dest_name
                );

            if ( $result )
            {
                $stats = stat( $dest_name );

                $this->Edit(
                    array(
                        'size' => $stats[7],
                        'storedname' => $stored_name
                        )
                    );

                if (
                    !strlen( $data['storedname'] )
                    and $fh = fopen( $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$stored_name.'.info', 'w' )
                    )
                {
                    fwrite( $fh, $data['realname'] );
                    fclose( $fh );
                }
            }
        }

        return $result;
    }

    function getFileType()
    {
        $result = '';

        if ( $this->mItemId )
        {
            $data = $this->getItem();

            require_once('mimetypes/MimeTypes.php');

            $mime = new MimeTypes();
            $result = $mime->get_File_Type( $data['realname'] );

            if ( !strlen( $result ) )
            {
                $tmp_dir = new InnoworkDocumentDirectory(
                    InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                    InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                    $data['directoryid']
                    );

                $result = $mime->get_File_Type(
                    $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$data['storedname']
                    );
            }
        }

        return $result;
    }

    function doTrash( $arg )
    {
        return true;
    }

    function Cut()
    {
        $result = false;

        if ( $this->mItemId )
        {
            require_once('innomatic/datatransfer/Clipboard.php');

            $clip = new ClipBoard(
                Clipboard::TYPE_ARRAY,
                '',
                0,
                'innowork-docs',
                InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId(),
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName()
                );

            $item['type'] = $this->mItemType;
            $item['id'] = $this->mItemId;
            $item['action'] = 'cut';

            $result = $clip->Store(
                $item
                );
        }

        return $result;
    }

    public function convertTo($type) {
        $result = false;
        if ($this->mItemId and $this->mConvertible) {
            require_once('innowork/core/InnoworkCore.php');
        	$tmp_innoworkcore = InnoworkCore::instance('innoworkcore', InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess());
            $summaries = $tmp_innoworkcore->GetSummaries();
            $class_name = $summaries[$type]['classname'];
			if (!class_exists($class_name)) {
				return false;
			}
            $tmp_class = new $class_name(InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess());

            if ($tmp_class->mConvertible) {
                $real_data = $this->getItem();

        $innowork_dir = new InnoworkDocumentDirectory(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
            $real_data['directoryid']
            );

        $fname = $innowork_dir->getBasePath().$innowork_dir->getStoredPath().$real_data['storedname'];

                $generic_data['companyid'] = $real_data[$this->mGenericFields['companyid']];
                $generic_data['projectid'] = $real_data[$this->mGenericFields['projectid']];
                $generic_data['title'] = $real_data[$this->mGenericFields['title']];
                $generic_data['content'] = file_get_contents($fname);
                $generic_data['binarycontent'] = file_get_contents($fname);
                $result = $tmp_class->ConvertFrom($generic_data);
            }
        }
        return $result;
    }
    
    public function convertFrom($genericData) {
        $result = false;
        if ($this->mConvertible) {

            if (strlen($this->mGenericFields['companyid']))
                $real_data[$this->mGenericFields['companyid']] = $genericData['companyid'];
            if (strlen($this->mGenericFields['projectid']))
                $real_data[$this->mGenericFields['projectid']] = $genericData['projectid'];
            if (strlen($this->mGenericFields['title']))
                $real_data[$this->mGenericFields['title']] = $genericData['title'];
            $result = $this->Create($real_data);
            
            $tmp_fname = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/innowork-docs/'.md5( microtime() );
            if ($fh = fopen($tmp_fname, 'wb')) {
                if (strlen($genericData['binarycontent'])) fwrite($fh, $genericData['binarycontent']);
                else fwrite($fh, $genericData['content']);
                fclose($fh);
                $this->setFile($tmp_fname);
                unlink($tmp_fname);
            }
        }
        return $result;
    }
}
?>