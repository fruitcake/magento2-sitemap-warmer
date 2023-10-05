<?php

namespace Fruitcake\SitemapWarmer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection as SitemapCollection;
use Magento\Sitemap\Model\Sitemap;

class WarmCache extends Command
{
    /**
     * @var Curl
     */
    protected $curl;

    protected $sitemapCollectionFactory;

    /**
     * constructor.
     * @param Curl $curl
     */
    public function __construct(
        Curl $curl,
        CollectionFactory $sitemapCollectionFactory
    ) {
        $this->curl = $curl;
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('sitemap:warm');
        $this->setDescription('Warm sitemap');
        $this->addArgument('sitemaps', InputArgument::IS_ARRAY, 'List of sitemaps (empty to use Magento sitemaps');
        $this->addOption('priority', 'P', InputOption::VALUE_OPTIONAL, 'Minimum Priority, eg 0.5');
        $this->addOption('requests', 'R', InputOption::VALUE_OPTIONAL, 'Maximum number of requests');
        $this->addOption('sleep', 'S', InputOption::VALUE_OPTIONAL, 'Sleep between requests (in sec)');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $maxRequests = $input->getOption('requests') ?: 100;
        $priority = $input->getOption('priority') ?: null;
        $sleep = (int) $input->getOption('sleep') ?: 0;

        $sitemaps = $input->getArgument('sitemaps') ?: [];

        if (count($sitemaps) === 0) {
            $sitemaps = [];
            /** @var SitemapCollection $collection */
            $collection = $this->sitemapCollectionFactory->create();

            /** @var Sitemap $sitemap */
            foreach ($collection->getItems() as $sitemap) {
                $sitemaps[] = $sitemap->getSitemapUrl(
                    $sitemap->getData('sitemap_path'),
                    $sitemap->getData('sitemap_filename')
                );
            }
        }

        if (count($sitemaps) === 0) {
            throw new \RuntimeException('No sitemaps found..');
        }

        $this->curl->setHeaders(['User-Agent' => 'Fruitcake/SitemapWarmer Magento/2 PHP/Curl']);

        $urls = [];
        foreach ($sitemaps as $sitemapUrl) {
            try {
                $this->curl->get($sitemapUrl);
                $xml = simplexml_load_string($this->curl->getBody());
                $data = json_decode(json_encode($xml), true);
                if (!isset($data['url'])) {
                    throw new \RuntimeException('Invalid xml..');
                }

                $urls = array_merge($urls, $data['url']);
            } catch (\Exception $e) {
                $output->writeln('Cannot parse ' . $sitemapUrl . ': ' . $e->getMessage());
            }
        }

        // Check valid URLs
        $urls = array_filter($urls, function ($url) use ($priority) {
            return isset($url['loc']) && filter_var($url['loc'], FILTER_VALIDATE_URL);
        });

        $output->writeln('Found ' . count($urls) . ' URLs  from ' . count($sitemaps) . ' sitemaps');

        // Filter on priority if set
        if ($priority !== null) {
            $urls = array_filter($urls, function ($url) use ($priority) {
                return ($url['priority'] ?? 1) >= $priority;
            });

            $output->writeln('Found ' . count($urls) . ' URLS with at least priority ' . $priority);
        }

        // Shuffle
        shuffle($urls);

        // Take $maxRequests
        $urls = array_slice($urls, 0, $maxRequests);

        $startTime = microtime(true);
        foreach ($urls as $url) {
            $time = microtime(true);
            $output->write($url['loc'] . ' ... ');
            $this->curl->get($url['loc']);
            $output->write($this->curl->getStatus() . ' / ' . round(microtime(true) - $time, 3) . 's');
            $output->write(PHP_EOL);
            if ($sleep > 0) {
                sleep($sleep);
            }
        }

        $output->writeln('Done in ' . round(microtime(true) - $startTime, 1) . ' seconds');
    }

}
