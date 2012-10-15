<?php

use GW2Spidy\DB\User;

use \DateTime;

use GW2Spidy\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

use GW2Spidy\DB\DisciplineQuery;
use GW2Spidy\DB\ItemSubTypeQuery;
use GW2Spidy\DB\ItemType;
use GW2Spidy\DB\RecipeQuery;
use GW2Spidy\DB\GW2Session;
use GW2Spidy\DB\GoldToGemRateQuery;
use GW2Spidy\DB\GemToGoldRateQuery;
use GW2Spidy\DB\ItemQuery;
use GW2Spidy\DB\ItemTypeQuery;
use GW2Spidy\DB\SellListingQuery;
use GW2Spidy\DB\WorkerQueueItemQuery;
use GW2Spidy\DB\ItemPeer;
use GW2Spidy\DB\BuyListingPeer;
use GW2Spidy\DB\SellListingPeer;
use GW2Spidy\DB\BuyListingQuery;
use GW2Spidy\DB\Watchlist;
use GW2Spidy\DB\WatchlistQuery;

use GW2Spidy\Util\Functions;

/**
 * ----------------------
 *  route /watchlistadd POST
 * ----------------------
 */
$app->post("/watchlist/add", function (Request $request) use ($app) {
    if (!$app['security']->getToken() || !($user = $app['security']->getToken()->getUser()) || !$user instanceof User) {
        return $app->redirect($app['url_generator']->generate('login'));
    }

    $data_id = $request->get("data_id");
    if($data_id){

        // TODO: Test if item is valid item!

        // check unique
        if (!WatchlistQuery::create()->filterByUser($user)->filterByItemId($data_id)->count()) {
            $w = new Watchlist();
            $w->setUser($user);
            $w->setItemId($data_id);

            $w->save();
        }
    }
    //TODO: check what happens when referer is disabled in browser
    $uri = $request->headers->get('referer') ?: $app['url_generator']->generate('item', array('dataId' => $data_id));
    return $app->redirect($uri);
})
->bind('watchlistaddpost');

/**
 * ----------------------
 *  route /watchlistremove POST
 * ----------------------
 */
$app->post("/watchlist/remove", function (Request $request) use ($app) {
    if (!$app['security']->getToken() || !($user = $app['security']->getToken()->getUser()) || !$user instanceof User) {
        return $app->redirect($app['url_generator']->generate('login'));
    }

    $data_id = $request->get("data_id");
    if($data_id){

        // TODO: Test if item is valid item!

        $w = WatchlistQuery::create();
        $w->filterByItemId($data_id);
        $w->filterByUser($user);
        $w->delete();
    }
    return $app->redirect($app['url_generator']->generate('watchlist'));
})
->bind('watchlistremovepost');

/**
 * ----------------------
 *  route /watchlist GET
 * ----------------------
 */
$app->get("/watchlist/{page}", function(Request $request, $page) use($app) {
    if (!$app['security']->getToken() || !($user = $app['security']->getToken()->getUser()) || !$user instanceof User) {
        return $app->redirect($app['url_generator']->generate('login'));
    }

    $itemIds = array();
    if ($ws = $user->getWatchlists()) {
        foreach ($ws as $w) {
            $itemIds[] = $w->getItemId();
        }
    }

    $page = $page > 0 ? $page : 1;

    $q = ItemQuery::create();
    $q->filterByPrimaryKeys(array_unique($itemIds));

    // use generic function to render
    return item_list($app, $request, $q, $page, 25, array('watchlist'=> true));
})
->assert('page', '\d*')
->value('page', 1)
->bind('watchlist');

