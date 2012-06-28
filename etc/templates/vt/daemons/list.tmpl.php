<?
    $__pageTitle = LocaleLoader::Translate( "vt.daemons.header");
    $daemons = array( 'sync-sources', 'process-queue', 'sync-top' );

    $__breadcrumbs = array( array( 'link' => Site::GetWebPath( "vt://daemons/list/" ), 'title' => $__pageTitle ) );
?>
{increal:tmpl://vt/header.tmpl.php}
<div class="main">
	<div class="inner">
        {increal:tmpl://vt/elements/menu/breadcrumbs.tmpl.php}
        <div class="pagetitle">
            <h1>{$__pageTitle}</h1>
        </div>

<table class="objects">
    <thead>
        <tr>
            <th>{lang:vt.daemons.title}</th>
            <th>{lang:vt.daemons.description}</th>
            <th>{lang:vt.daemons.time}</th>
            <th>{lang:vt.daemons.link}</th>
        </tr>
    </thead>
<?php
    foreach ( $daemons as $name )  {
        $link  = 'daemons://' . $name . '/';
?>
        <tr>
            <td><strong>{$name}</strong></td>
            <td><?= LocaleLoader::Translate( 'vt.daemons.list.' . str_replace( '-', '', $name ) . '.title' ) ?></td>
            <td><?= LocaleLoader::Translate( 'vt.daemons.list.' . str_replace( '-', '', $name ) . '.time' ) ?></td>
            <td class="left">
                <? if ( empty( $daemon['disabled'] ) ) { ?><a href="{web:$link}" target="_blank">{web:$link}</a>  <? } ?>
            </td>
        </tr>
<?php
    }
?>
</table>
</div>
{increal:tmpl://vt/footer.tmpl.php}