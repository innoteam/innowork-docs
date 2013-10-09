<?php
/*
 *   Copyright (C) 2003-2009 Innoteam
 *
 */

$domain_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->Execute(
    'SELECT domainid '.
    'FROM domains '.
    'WHERE id='.$domainid
);

if ( !file_exists( InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domain_query->getFields( 'domainid' ).'/innowork-docs-files/' ) ) {
	mkdir( InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domain_query->getFields( 'domainid' ).'/innowork-docs-files/', 0755 );
}

?>
