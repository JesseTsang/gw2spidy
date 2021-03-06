<?php

use Symfony\Component\HttpFoundation\Request;

use GW2Spidy\DB\DisciplineQuery;
use GW2Spidy\DB\RecipeQuery;

/**
 * ----------------------
 *  route /crafting
 * ----------------------
 */
$app->get("/crafting/{discipline}/{page}", function(Request $request, $discipline, $page) use($app) {
    $app->setCraftingActive();

    $page = $page > 0 ? $page : 1;

    $q = RecipeQuery::create();

    if ($discipline == -1) {
        $discipline = null;
    }

    if (!is_null($discipline)) {
        $discipline = DisciplineQuery::create()->findPk($discipline);
        $q->filterByDiscipline($discipline);
    }

    // use generic function to render
    return recipe_list($app, $request, $q, $page, 50, array('discipline' => $discipline));
})
->assert('discipline', '-?\d*')
->assert('page',       '-?\d*')
->value('page', 1)
->bind('crafting');

/**
 * ----------------------
 *  route /recipe
 * ----------------------
 */
$app->get("/recipe/{dataId}", function(Request $request, $dataId) use($app) {
    $app->setCraftingActive();
    $recipe = RecipeQuery::create()->findPK($dataId);

    if (!$recipe) {
        return $app->abort(404, "Page does not exist.");
    }

    $item = $recipe->getResultItem();
    if(!$item) {
        return $app->abort(404, "Recipe not supported yet, we don't have the resulting item in the database yet [[ {$recipe->getName()} ]] [[ {$recipe->getResultItemId()} ]] ");
    }


    $tree = buildMultiRecipeTree($item, $app, $recipe);

    return $app['twig']->render('recipe.html.twig', array(
        'recipe' => $recipe,
        'tree' => json_encode($tree),
    ));
})
->assert('dataId', '\d+')
->bind('recipe');

