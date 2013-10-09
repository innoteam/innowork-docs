<?php
require_once('innowork/core/InnoworkItem.php');

class InnoworkDocumentDirectory extends InnoworkItem {
	var $mTable = 'innowork_docs_dirs';
	/*
	 var $mNewDispatcher = 'view';
	 var $mNewEvent = 'newdirectory';
	 */

	var $mParentId;
	var $mDirectoryName;
	var $mNoTrash = false;
	const ITEM_TYPE = 'documentdirectory';

	function InnoworkDocumentDirectory(
	$rrootDb,
	$rdomainDA,
	$directoryId = 0
	)
	{
		parent::__construct(
		$rrootDb,
		$rdomainDA,
		InnoworkDocumentDirectory::ITEM_TYPE,
		$directoryId
		);

		//$this->mKeys['parentid'] = 'table:innowork_docs_dirs:name:integer';
		//$this->mKeys['customerid'] = 'table:innowork_directory_companies:companyname:integer';
		//$this->mKeys['projectid'] = 'table:innowork_projects:name:integer';
		//$this->mKeys['realname'] = 'text';
		//$this->mKeys['description'] = 'text';
		$this->mKeys['directoryname'] = 'text';
		$this->mKeys['parentid'] = 'integer';

		$this->mSearchResultKeys[] = 'directoryname';

		$this->mViewableSearchResultKeys[] = 'directoryname';

		$this->mSearchOrderBy = 'directoryname';
		$this->mShowDispatcher = 'view';
		$this->mShowEvent = 'showdir';
	}

	function doCreate(
	$params,
	$userId
	)
	{
		$result = false;

		/*
		 if (
		 !isset($params['projectid'] )
		 or !strlen( $params['projectid'] )
		 ) $params['projectid'] = '0';

		 if (
		 !isset($params['customerid'] )
		 or !strlen( $params['customerid'] )
		 ) $params['customerid'] = '0';
		 */

		if ( count( $params ) )
		{


			if (
			isset($params['directoryname'] )
			and !strlen( $params['directoryname'] )
			) unset( $params['directoryname'] );

			if (
			!isset($params['storedname'] )
			or !strlen( $params['storedname'] )
			) $params['storedname'] = md5( microtime() );


			$item_id = $this->mrDomainDA->getNextSequenceValue( $this->mTable.'_id_seq' );

			$key_pre = $value_pre = $keys = $values = '';

			while ( list( $key, $val ) = each( $params ) )
			{
				$key_pre = ',';
				$value_pre = ',';

				switch ( $key )
				{
					case 'directoryname':
					case 'storedname':
						$keys .= $key_pre.$key;
						$values .= $value_pre.$this->mrDomainDA->formatText( $val );
						break;

					case 'parentid':
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

				if ( isset($params['directoryname'] ) and !strlen( $params['directoryname'] ) ) unset( $params['directoryname'] );

				while ( list( $field, $value ) = each( $params ) )
				{
					if ( $field != 'id' )
					{
						switch ( $field )
						{
							case 'directoryname':
							case 'storedname':
								if ( !$start ) $update_str .= ',';
								$update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
								$start = 0;
								break;

							case 'parentid':
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
					isset($params['directoryname'] )
					and strlen( $params['directoryname'] )
					)
					{
						$data = $this->getItem();

						if ( $fh = fopen(
						$this->getBasePath().substr( $this->getStoredPath(), 0, -1 ).'.info',
                                'w' ) )
						{
							fwrite( $fh, $params['directoryname'] );
							fclose( $fh );
						}
					}

					$result = TRUE;
				}
			}
		}

		return $result;
	}

	function doRemove(
	$userId
	)
	{
		$result = false;

		$all_removed = true;

		// Directories

		$dirs_query = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->Execute(
            'SELECT id '.
            'FROM innowork_docs_dirs '.
            'WHERE parentid='.$this->mItemId );

		while ( !$dirs_query->eof )
		{
			$tmp_dir = new InnoworkDocumentDirectory(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$dirs_query->getFields( 'id' )
			);

			if ( !$tmp_dir->Remove() )
			{
				$all_removed = false;
			}
			unset( $tmp_dir );
			$dirs_query->moveNext();
		}

		$dirs_query->free();

		// Documents

		$docs_query = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->Execute(
            'SELECT id '.
            'FROM innowork_docs '.
            'WHERE directoryid='.$this->mItemId );

		while ( !$docs_query->eof )
		{
			$tmp_doc = new InnoworkDocument(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$docs_query->getFields( 'id' )
			);

			if ( !$tmp_doc->Remove() )
			{
				$all_removed = false;
			}
			unset( $tmp_doc );
			$docs_query->moveNext();
		}

		$docs_query->free();

		if ( $all_removed )
		{
			rmdir( $this->getBasePath().$this->getStoredPath() );
			unlink( $this->getBasePath().substr( $this->getStoredPath(), 0, -1 ).'.info' );

			$result = $this->mrDomainDA->Execute(
                'DELETE FROM '.$this->mTable.' '.
                'WHERE id='.$this->mItemId
			);
		}

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

	function MkDir()
	{
		$result = false;

		if ( $this->mItemId )
		{
			if ( !file_exists( $this->getBasePath().$this->getStoredPath() ) )
			{
				$result = mkdir( $this->getBasePath().$this->getStoredPath(), 0755 );

				$data = $this->getItem();

				if (
				$result
				and $fh = fopen( $this->getBasePath().substr( $this->getStoredPath(), 0, -1 ).'.info', 'w' )
				)
				{
					fwrite( $fh, $data['directoryname'] );
					fclose( $fh );
				}
			}
		}

		return $result;
	}

	function getRealPath()
	{
		$result = '';

		$dir_data = $this->getItem();


		if ( $dir_data['parentid'] != 0 )
		{
			$tmp_dir = new InnoworkDocumentDirectory(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$dir_data['parentid']
			);
			$result = $tmp_dir->getRealPath().$dir_data['directoryname'].'/';
			unset( $tmp_dir );
		}
		else $result = '/'.$dir_data['directoryname'].'/';

		return $result;
	}

	function getStoredPath()
	{
		$result = '';

		$dir_data = $this->getItem();

		if ( $dir_data['parentid'] != 0 )
		{
			$tmp_dir = new InnoworkDocumentDirectory(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$dir_data['parentid']
			);
			$result = $tmp_dir->getStoredPath().$dir_data['storedname'].'/';
			unset( $tmp_dir );
		}
		else $result = $dir_data['storedname'].'/';

		return $result;
	}

	function getBasePath()
	{
		return InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId().'/innowork-docs-files/';
	}

	function AddArchivedTree(
	$archive,
	$keywords = '',
	$projectId = 0,
	$customerId = 0
	)
	{
		$result = false;

		if (
		file_exists( $archive )
		)
		{
			require_once('innomatic/io/archive/Archive.php');

			$dest_dir = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/innowork-docs/'.md5( microtime() );
			mkdir( $dest_dir );

			$arc = new Archive(
			$archive,
			Archive::FORMAT_TGZ
			);
			$arc->Extract( $dest_dir );

			$this->_AddTreeNode(
			$this->mItemId,
			$dest_dir,
			$keywords,
			$projectId,
			$customerId,
			false
			);

			require_once('innomatic/io/filesystem/DirectoryUtils.php');
			DirectoryUtils::unlinkTree( $dest_dir );
		}

		return $result;
	}

	function _AddTreeNode(
	$directoryId,
	$node,
	$keywords,
	$projectId,
	$customerId,
	$create = true
	)
	{
		if ( is_dir( $node ) )
		{
			$tmp_id = $directoryId;

			if ( $create )
			{
				$tmp_dir = new InnoworkDocumentDirectory(
				InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
				InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
				);

				$tmp_dir->Create(
				array(
                        'directoryname' => basename( $node ),
                        'parentid' => $directoryId,
                        'keywords' => $keywords,
                        'projectid' => $projectId,
                        'customerid' => $customerId
				)
				);

				if ( $directoryId != 0 )
				{
					$tmp_dir->mAcl->CopyAcl(
                        'documentdirectory',
					$directoryId
					);
				}

				$tmp_dir->MkDir();

				$tmp_id = $tmp_dir->mItemId;
			}

			if ( $dh = opendir( $node ) )
			{
				while ( ( $file = readdir( $dh ) ) !== false )
				{
					if ( $file == '.' or $file == '..' ) continue;

					$this->_AddTreeNode(
					$tmp_id,
					$node.'/'.$file,
					$keywords,
					$projectId,
					$customerId,
					true
					);
				}

				closedir( $dh );
			}
		}
		else
		{
			$tmp_doc = new InnoworkDocument(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
			);

			$tmp_doc->Create(
			array(
                    'realname' => basename( $node ),
                    'directoryid' => $directoryId,
                    'keywords' => $keywords,
                    'projectid' => $projectId,
                    'customerid' => $customerId
			)
			);

			if ( $directoryId != 0 )
			{
				$tmp_doc->mAcl->CopyAcl(
                    'documentdirectory',
				$directoryId
				);
			}

			$tmp_doc->setFile(
			$node
			);
		}
	}

	/*
	 function Remove()
	 {
	 $result = false;

	 if (
	 $this->mId
	 and
	 InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->Execute(
	 'DELETE FROM innowork_billing_vat '.
	 'WHERE id='.$this->mId
	 )
	 )
	 {
	 InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->Execute(
	 'UPDATE innowork_billing_invoices_rows '.
	 'SET vatid=0 '.
	 'WHERE vatid='.$this->mId
	 );

	 $sets = new InnoworkBillingSettingsHandler ();

	 if ( $sets->getDefaultVat() == $this->mId )
	 {
	 $sets->setDefaultVat( '0' );
	 }

	 $this->mId = 0;
	 $this->mDescription = $this->mPercentual = '';

	 $result = true;
	 }

	 return $result;
	 }
	 */

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

	function Paste()
	{
		$result = false;

		//return false;

		require_once('innomatic/datatransfer/Clipboard.php');

		$clip = new ClipBoard(
		Clipboard::TYPE_ARRAY,
                '',
		0,
                'innowork-docs',
		InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId(),
		InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName()
		);

		if ( $clip->IsValid() )
		{
			$result = $clip->Retrieve();

			if ( is_array( $result ) )
			{
				require_once('innomatic/process/Semaphore.php');

				$sem = new Semaphore(
                        'innowork-docs',
                        'repository'
                        );
                        $sem->WaitGreen();
                        $sem->setRed();

                        $type = $result['type'];

                        $class_name = $result['type'] == InnoworkDocumentDirectory::ITEM_TYPE ? 'InnoworkDocumentDirectory' : 'InnoworkDocument';
						if (!class_exists($class_name)) {
							return false;
						}
                        $tmp_class = new $class_name(
                        InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                        InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                        $result['id']
                        );

                        $fields[$result['type'] == InnoworkDocumentDirectory::ITEM_TYPE ? 'parentid' : 'directoryid'] = $this->mItemId;

                        switch ( $type )
                        {
                        	case InnoworkDocumentDirectory::ITEM_TYPE:
                        		$old_path = $tmp_class->getBasePath().$tmp_class->getStoredPath();
                        		$result = $tmp_class->Edit( $fields );
                        		$new_path = $tmp_class->getBasePath().$tmp_class->getStoredPath();

                        		if ( $old_path != $new_path )
                        		{
                        			require_once('innomatic/io/filesystem/DirectoryUtils.php');
                        			DirectoryUtils::dircopy( $old_path, $new_path );
                        			DirectoryUtils::unlinkTree( $old_path );
                        		}
                        		break;

                        	default:
                        		$doc_data = $tmp_class->getItem();

                        		$tmp_dir = new InnoworkDocumentDirectory(
                        		InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                        		InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
                        		$doc_data['directoryid']
                        		);

                        		$old_path = $tmp_dir->getBasePath().$tmp_dir->getStoredPath().$doc_data['storedname'];
                        		$new_path = $this->getBasePath().$this->getStoredPath().$doc_data['storedname'];

                        		$result = $tmp_class->Edit( $fields );

                        		if ( $old_path != $new_path )
                        		{
                        			copy( $old_path, $new_path );
                        			unlink( $old_path );
                        		}
                        		break;
                        }

                        $clip->Erase();
                        $sem->setGreen();
			}
			else $result = false;
		}

		return $result;
	}
}
?>