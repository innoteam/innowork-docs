<?php
/*
 *   Copyright (C) 2003-2009 Innoteam
 *
 */

if ( isset($domainid ) and strlen( $domainid ) ) {
	$domain_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->Execute(
    'SELECT domainid '.
    'FROM domains '.
    'WHERE id='.$domainid
	);

	if ( file_exists( InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domain_query->getFields( 'domainid' ).'/innowork-docs-files/' ) ) {
		require_once('innomatic/io/filesystem/DirectoryUtils.php');
		DirectoryUtils::unlinkTree( InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domain_query->getFields( 'domainid' ).'/innowork-docs-files/' );
	}
}

?>
