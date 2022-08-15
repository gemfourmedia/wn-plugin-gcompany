<?php

namespace GemFourMedia\GCompany\Classes;

use Carbon\Carbon;
use Cms\Classes\Controller;
use DB;
use Illuminate\Database\Eloquent\Collection;
use OFFLINE\SiteSearch\Classes\Result;
use OFFLINE\SiteSearch\Models\Settings;
use GemFourMedia\GCompany\Models\Article;
use Throwable;
use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;

/**
 * Searches the contents generated by the
 * GemFourMedia.GCompany plugin
 *
 * @package OFFLINE\SiteSearch\Classes\Providers
 */
class GCompanySearchResultsProvider extends ResultsProvider
{

    /**
     * Runs the search for this provider.
     *
     * @return ResultsProvider
     */
    public function search()
    {
        if ( ! $this->isInstalledAndEnabled()) {
            return $this;
        }

        foreach ($this->getArticles() as $article) {
            // Make this result more relevant, if the query is found in the title
            $relevance = mb_stripos($article->title, $this->query) === false ? 1 : 2;

            if ($relevance > 1 && $article->published_at) {
                // Make sure that `published_at` is a Carbon object
                $publishedAt = $article->published_at;
                if (is_string($publishedAt)) {
                    try {
                        $publishedAt = Carbon::parse($publishedAt);
                    } catch (Throwable $e) {
                        // If parsing fails use the current date.
                        $publishedAt = Carbon::now();
                    }
                }
                $relevance -= $this->getAgePenalty($publishedAt->diffInDays(Carbon::now()));
            }

            $result        = new Result($this->query, $relevance);
            $result->title = $article->title;
            $result->text  = $article->introtext;
            $result->meta  = $article->published_at;
            $result->model = $article;
            $result->url = $article->getFrontEndUrl();
            $result->thumb = isset($article->main_image) ? $article->main_image : null;
            
            $this->addResult($result);
        }

        return $this;
    }

    /**
     * Get all articles with matching title or content.
     *
     * @return Collection
     */
    protected function getArticles()
    {
        // If Rainlab.Translate is not installed or we are currently,
        // using the default locale we simply query the default table.
        $translator = $this->translator();

        if ( ! $translator || $translator->getDefaultLocale() === $translator->getLocale()) {

            return $this->itemsFromDefaultLocale();
        }

        // If Rainlab.Translate is available we also have to
        // query the rainlab_translate_attributes table for translated
        // contents since the title and content attributes on the Post
        // model are not indexed.
        return $this->itemsFromCurrentLocale();
    }

    /**
     * Returns all matching articles from the default locale.
     * Translated attributes are ignored.
     *
     * @return Collection
     */
    protected function itemsFromDefaultLocale()
    {
        return $this->defaultModelQuery()
                    ->where(function ($query) {
                        $query->where('title', 'like', "%{$this->query}%")
                              ->orWhere('content', 'like', "%{$this->query}%")
                              ->orWhere('subtitle', 'like', "%{$this->query}%")
                              ->orWhere('introtext', 'like', "%{$this->query}%");
                              // ->orWhereHas('blocks', function($q) {
                              //       $q->where('title', 'like', "%{$this->query}%")
                              //         ->orWhere('subtitle', 'like', "%{$this->query}%")
                              //         ->orWhere('content', 'like', "%{$this->query}%");
                              // });
                    })
                    ->get();
    }

    /**
     * Returns all matching articles with translated contents.
     *
     * @return Collection
     */
    protected function itemsFromCurrentLocale()
    {
        // First fetch all model ids with maching contents.
        $results = DB::table('winter_translate_attributes')
                     ->where('locale', $this->currentLocale())
                     ->where('model_type', Article::class)
                     ->where('attribute_data', 'LIKE', "%{$this->query}%")
                     ->get(['model_id']);

        $ids = collect($results)->pluck('model_id');

        // Then return all maching articles via Eloquent.
        return $this->defaultModelQuery()->whereIn('id', $ids)->get();
    }

    /**
     * This is the default "base query" for quering
     * matching models.
     */
    protected function defaultModelQuery()
    {
        return Article::published()->with(['images', 'category', 'blocks']);
    }

    /**
     * Checks if the RainLab.Blog Plugin is installed and
     * enabled in the config.
     *
     * @return bool
     */
    protected function isInstalledAndEnabled()
    {
        return $this->isPluginAvailable($this->identifier);
    }


    /**
     * Display name for this provider.
     *
     * @return mixed
     */
    public function displayName()
    {
        return 'Article';
    }

    /**
     * Returns the plugin's identifier string.
     *
     * @return string
     */
    public function identifier()
    {
        return 'GemFourMedia.GCompany';
    }

    /**
     * Return the current locale
     *
     * @return string|null
     */
    protected function currentLocale()
    {
        $translator = $this->translator();

        if ( ! $translator) {
            return null;
        }

        return $translator->getLocale();
    }
}
