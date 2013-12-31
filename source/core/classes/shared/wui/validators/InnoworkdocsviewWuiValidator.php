<?php
/*
 *   Copyright (C) 2003-2009 Innoteam
 *
 */

require_once('innomatic/wui/Wui.php');
if (
isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocsaction'] )
)
{
	$wui = Wui::instance('wui');
	$wui->loadWidget('sessionkey');

	switch ( Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocsaction'] )
	{
		case 'viewoptions':
			$docs_viewby_sk = new WuiSessionKey(
            'innoworkdocs_viewby',
			array(
                'value' => Wui::instance('wui')->parameters['wui']['wui']['evd']['viewby']
			)
			);
			$docs_orderby_sk = new WuiSessionKey(
            'innoworkdocs_orderby',
			array(
                'value' => Wui::instance('wui')->parameters['wui']['wui']['evd']['orderby']
			)
			);
			$docs_orderby_sortorder_sk = new WuiSessionKey(
            'innoworkdocs_orderby_sortorder',
			array(
                'value' => Wui::instance('wui')->parameters['wui']['wui']['evd']['sortorder']
			)
			);
			$docs_orderby_orderdirs_sk = new WuiSessionKey(
            'innoworkdocs_orderby_orderdirs',
			array(
                'value' => Wui::instance('wui')->parameters['wui']['wui']['evd']['orderdirs']
			)
			);
			break;

		case 'chdir':
			$docs_dir_sk = new WuiSessionKey(
            'innoworkdocs_dir',
			array(
                'value' => Wui::instance('wui')->parameters['wui']['wui']['evd']['directoryid']
			)
			);
			break;

		case 'mkdir':
			if ( strlen( Wui::instance('wui')->parameters['wui']['wui']['evd']['dirname'] ) )
			{
				require_once('innowork/docs/InnoworkDocumentDirectory.php');

				$innowork_dir = new InnoworkDocumentDirectory(
				\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
				\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
				);

				$innowork_dir->Create(
				array(
                    'directoryname' => Wui::instance('wui')->parameters['wui']['wui']['evd']['dirname'],
                    'parentid' => Wui::instance('wui')->parameters['wui']['wui']['evd']['parentid']
				)
				);

				if ( Wui::instance('wui')->parameters['wui']['wui']['evd']['parentid'] != 0 )
				{
					$innowork_dir->mAcl->CopyAcl(
                    'documentdirectory',
					Wui::instance('wui')->parameters['wui']['wui']['evd']['parentid']
					);
				}

				$innowork_dir->MkDir();
			}
			break;

		case 'removedir':
			require_once('innowork/docs/InnoworkDocumentDirectory.php');

			$innowork_doc = new InnoworkDocumentDirectory(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
			Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocs-dir-id']
			);

			$innowork_doc->Trash();

			break;

		case 'addfile':
			if (
			isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['newfile']['tmp_name'] )
			and strlen( Wui::instance('wui')->parameters['wui']['wui']['evd']['newfile']['tmp_name'] )
			)
			{
				require_once('innowork/docs/InnoworkDocumentDirectory.php');
					
				if (
				isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdirasfile'] )
				and Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdirasfile'] == 'true'
				)
				{
					$innowork_dir = new InnoworkDocumentDirectory(
					\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
					\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
					Wui::instance('wui')->parameters['wui']['wui']['evd']['directoryid']
					);
					$innowork_dir->AddArchivedTree(
					Wui::instance('wui')->parameters['wui']['wui']['evd']['newfile']['tmp_name'],
					Wui::instance('wui')->parameters['wui']['wui']['evd']['keywords'],
					Wui::instance('wui')->parameters['wui']['wui']['evd']['projectid'],
					Wui::instance('wui')->parameters['wui']['wui']['evd']['customerid']
					);
				}
				else
				{
					require_once('innowork/docs/InnoworkDocument.php');
					$innowork_doc = new InnoworkDocument(
					\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
					\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
					);

					$innowork_doc->Create(
					array(
                'keywords' => Wui::instance('wui')->parameters['wui']['wui']['evd']['keywords'],
                'directoryid' => Wui::instance('wui')->parameters['wui']['wui']['evd']['directoryid'],
                'realname' => Wui::instance('wui')->parameters['wui']['wui']['evd']['newfile']['name'],
                'projectid' => Wui::instance('wui')->parameters['wui']['wui']['evd']['projectid'],
                'customerid' => Wui::instance('wui')->parameters['wui']['wui']['evd']['customerid']
					)
					);

					if ( Wui::instance('wui')->parameters['wui']['wui']['evd']['directoryid'] != 0 )
					{
						$innowork_doc->mAcl->CopyAcl(
                'documentdirectory',
						Wui::instance('wui')->parameters['wui']['wui']['evd']['directoryid']
						);
					}

					$innowork_doc->setFile(
					Wui::instance('wui')->parameters['wui']['wui']['evd']['newfile']['tmp_name'],
					Wui::instance('wui')->parameters['wui']['wui']['evd']['newfile']['name']
					);
				}

				unlink( Wui::instance('wui')->parameters['wui']['wui']['evd']['newfile']['tmp_name'] );
			}

			break;

		case 'removefile':
			require_once('innowork/docs/InnoworkDocument.php');

			$innowork_doc = new InnoworkDocument(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
			Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocs-doc-id']
			);

			$innowork_doc->Trash();

			break;

		case 'getfile':
			require_once('innowork/docs/InnoworkDocument.php');
			require_once('innowork/docs/InnoworkDocumentDirectory.php');

			$innowork_doc = new InnoworkDocument(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
			Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocs-doc-id']
			);

			$innowork_doc_data = $innowork_doc->getItem();

			$innowork_dir = new InnoworkDocumentDirectory(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
			$innowork_doc_data['directoryid']
			);

			$fname = $innowork_dir->getBasePath().$innowork_dir->getStoredPath().$innowork_doc_data['storedname'];

			if ( file_exists( $fname ) )
			{
				$buf = file_get_contents($fname);

				$file_type = $innowork_doc->getFileType();
				if ( !strlen( $file_type ) ) $file_type = 'application/x-octet-stream';

				header( 'Content-Type: '.$file_type );
				header( 'Cache-Control:' );
				header( 'Content-Length: '.strlen( $buf ) );
				header( 'Content-Disposition: attachment; filename='.$innowork_doc_data['realname'] );
				header( 'Pragma: no-cache' );
				print( $buf );

				\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->halt();
			}

			break;

		case 'setadvanced':
			$advanced_sk = new WuiSessionKey(
            'innoworkdocs_advanced',
			array(
                'value' => 'true'
                )
                );
                break;

		case 'setsimple':
			$advanced_sk = new WuiSessionKey(
            'innoworkdocs_advanced',
			array(
                'value' => 'false'
                )
                );
                break;

		case 'cutdir':
			require_once('innowork/docs/InnoworkDocumentDirectory.php');
				
			$innowork_doc = new InnoworkDocumentDirectory(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
			Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocs-dir-id']
			);

			$innowork_doc->Cut();

			break;

		case 'cutfile':
			require_once('innowork/docs/InnoworkDocument.php');
				
			$innowork_doc = new InnoworkDocument(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
			Wui::instance('wui')->parameters['wui']['wui']['evd']['innoworkdocs-doc-id']
			);

			$innowork_doc->Cut();

			break;

		case 'paste':
			require_once('innowork/docs/InnoworkDocumentDirectory.php');

			$innowork_doc = new InnoworkDocumentDirectory(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
			Wui::instance('wui')->parameters['wui']['wui']['evd']['directoryid']
			);

			$innowork_doc->Paste();

			break;
	}
}

?>
