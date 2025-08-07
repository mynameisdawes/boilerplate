<?php

namespace Vektor\OneCRM\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Vektor\OneCRM\Models\Product;
use Vektor\OneCRM\Models\ProductAttribute;

use function Laravel\Prompts\text;

class CreateProducts extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onecrm:create_products {csv} {--category_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new products in the CRM from an imported CSV';

    protected $file_stream;
    protected $category_id;
    protected $columns = [];
    protected $parents = [];
    protected $orphans = [];
    protected $existing_products = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setFile();

        $this->category_id = $this->option('category_id') ?? config('onecrm.product_category_id');

        $this->columns = fgetcsv($this->file_stream);

        $count = 0;

        // Read and process all lines
        while ($line = fgetcsv($this->file_stream, 0, ',', '"', shell_exec('\\'))) {
            try {
                $this->handleProduct($line);
            } catch (\Exception $e) {
                echo '<pre>';
                var_dump($line);
                echo '</pre>';
                echo '<pre>';
                var_dump($e->getMessage());
                echo '</pre>';
            }
            ++$count;
            if ($count > 0) {
                // if (($count < 100 && $count % 20 == 0) || $count % 50 == 0) {
                $this->info("Processed {$count} products");
                // }
            }
        }
        $this->info('Processed all lines');
        fclose($this->file_stream);

        // If for some reason a parent couldn't be found for any lines on the first loop (lines out of order)
        // Try once again now all products have been filled in
        if (count($this->orphans) > 0) {
            $this->info(count($this->orphans).' products without parents found, retrying these lines');
            $count = count($this->orphans);
            // Ensures only trying once to find a parent and can't get stuck in a loop
            for ($i = 0; $i < $count; ++$i) {
                $line = array_pop($this->orphans);
                $this->handleProduct($line);
            }
            if (count($this->orphans) > 0) {
                $this->info('Unable to find parent products for '.$count.' child products');
            } else {
                $this->info('Successfully found parents for all child products');
            }
        }
        $this->info('Process complete');
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [
            'csv' => fn () => $this->file_prompt(),
        ];
    }

    protected function file_prompt()
    {
        return text(
            label: 'Import CSV',
            validate: fn ($file) => match (true) {
                !file_exists($file) => 'File not found',
                'csv' != pathinfo($file, PATHINFO_EXTENSION) => 'Incorrect file type, please use a CSV',
                default => null
            }
        );
    }

    private function handleProduct($line)
    {
        $product_array = $this->productArray($line);

        $product = new Product();

        $additional_data = [
            'product_category_id' => $this->category_id,
            'tax_code_id' => $product_array['tax_code_id'] ?? '10007e7d-4513-be9b-4067-4d224b754033',
            'list_price' => $product_array['purchase_price'] ?? '0',
        ];

        $parent_id = null;
        if (isset($product_array['parent_sku']) && null != $product_array['parent_sku']) {
            // Is a child product
            try {
                $parent_id = $this->parents[$product_array['parent_sku']];
                $additional_data['parent_product_id'] = $parent_id;
            } catch (\Exception $e) {
                $existing_parent = $this->findParent($product_array['parent_sku']);
                if ($existing_parent) {
                    $additional_data['parent_product_id'] = $existing_parent['id'];
                    $this->parents[$product_array['parent_sku']] = $existing_parent['id'];
                } else {
                    // Parent has not been generated yet, or does not exist
                    $this->orphans[] = $line;
                }
            }
        } else {
            $this->updateExistingProducts($product_array['manufacturers_part_no']);
        }
        $product->fill(array_merge($product_array, $additional_data));

        $product_exists = array_values(array_filter($this->existing_products, function ($product) use ($product_array) {
            return $product['manufacturers_part_no'] == $product_array['manufacturers_part_no'];
        }));

        if (count($product_exists) > 0) {
            if ($product->updateCrm($product_exists[0]['id'])) {
                $product_response = ['id' => $product_exists[0]['id']];
                if (isset($product_array['parent_sku']) && '' == $product_array['parent_sku']) {
                    $this->parents[$product_array['manufacturers_part_no']] = $product_response['id'];
                } else {
                    $existing_attributes = $product->index_related_productattributes($product_response['id']);
                    foreach ($existing_attributes as $attribute) {
                        $_attribute = new ProductAttribute();
                        $data = [];
                        if ('Colour' == $attribute['name'] && isset($product_array['Colour Name']) && ($attribute['value'] != $product_array['Colour Name'] || $attribute['hex_code'] != $product_array['Hex'])) {
                            $data = [
                                'value' => $product_array['Colour Name'],
                                'hex_code' => $product_array['Hex'],
                            ];
                            $_attribute->updateCrm($attribute['id'], $data);
                        } elseif ('Size' == $attribute['name'] && isset($product_array['Size Code']) && ($attribute['value'] != $product_array['Size Code'])) {
                            $data = [
                                'value' => $product_array['Size Code'],
                            ];
                            $_attribute->updateCrm($attribute['id'], $data);
                        }
                    }
                }
            }
        } else {
            $product_response = $product->persist();
            if ($product_response) {
                if (isset($product_array['parent_sku']) && '' == $product_array['parent_sku']) {
                    $this->parents[$product_array['manufacturers_part_no']] = $product_response['id'];
                } else {
                    if (0 == count($product_exists)) {
                        $colour_attribute = new ProductAttribute();
                        if (isset($product_array['Colour Name'])) {
                            $colour_data = [
                                'product_id' => $product_response['id'],
                                'name' => 'Colour',
                                'value' => $product_array['Colour Name'],
                                'hex_code' => $product_array['Hex'],
                            ];
                            $colour_attribute->fill($colour_data);
                            $colour_attribute_response = $colour_attribute->persist();
                        }

                        if (isset($product_array['Size Code'])) {
                            $size_attribute = new ProductAttribute();
                            $size_data = [
                                'product_id' => $product_response['id'],
                                'name' => 'Size',
                                'value' => $product_array['Size Code'],
                            ];
                            $size_attribute->fill($size_data);
                            $size_attribute_response = $size_attribute->persist();
                        }
                    }
                }
            }
        }
    }

    private function fetchProductPage($sku, $page)
    {
        $product = new Product();

        return $product->index([
            'filters' => [
                'product_category_id' => $this->category_id,
                'manufacturers_part_no' => $sku,
            ],
            'fields' => [
                'manufacturers_part_no',
                'sort_order',
            ],
            'page' => $page,
        ]);
    }

    private function updateExistingProducts($sku)
    {
        $page = 1;
        $this->existing_products = [];
        do {
            $existing_products = $this->fetchProductPage($sku, $page);
            $this->existing_products = array_merge($this->existing_products, $existing_products);
            ++$page;
        } while (null != $existing_products);
    }

    private function findParent($sku)
    {
        $page = 1;
        do {
            $possible_parents = $this->fetchProductPage($sku, $page);
            foreach ($possible_parents as $candidate) {
                if ($candidate['manufacturers_part_no'] == $sku) {
                    return $candidate;
                }
            }
            ++$page;
        } while (null != $possible_parents);

        return false;
    }

    private function setFile()
    {
        $file_path = $this->argument('csv');
        while (!file_exists($file_path) || 'csv' != pathinfo($file_path, PATHINFO_EXTENSION)) {
            $this->error('Invalid file');
            $file_path = $this->file_prompt();
        }
        $this->info("Importing from: {$file_path}");
        $this->file_stream = fopen($file_path, 'r');
    }

    private function productArray($line)
    {
        $array = [];
        for ($i = 0; $i < count($this->columns); ++$i) {
            $line[$i] = trim($line[$i]);
            if ('' != $line[$i]) {
                $array[$this->columns[$i]] = $line[$i];
            }
        }

        return $array;
    }

    // private function cmykToHex($c, $m, $y, $k)
    private function cmykToHex($cmyk)
    {
        if ('' == $cmyk) {
            return '';
        }
        $arr = explode(' ', $cmyk);
        // $rgb = $this->cmykToRgb($c, $m, $y, $k);
        $rgb = $this->cmykToRgb($arr[0], $arr[1], $arr[2], $arr[3]);

        return $this->rgbToHex($rgb['r'], $rgb['g'], $rgb['b']);
    }

    private function cmykToRgb($c, $m, $y, $k)
    {
        // assumes values given as percentages
        $cyan = $c / 100;
        $magenta = $m / 100;
        $yellow = $y / 100;
        $black = $k / 100;

        $r = 1 - min(1, $cyan * (1 - $black) + $black);
        $g = 1 - min(1, $magenta * (1 - $black) + $black);
        $b = 1 - min(1, $yellow * (1 - $black) + $black);
        $r = round($r * 255);
        $g = round($g * 255);
        $b = round($b * 255);

        return [
            'r' => $r,
            'g' => $g,
            'b' => $b,
        ];
    }

    private function rgbToHex($r, $g, $b)
    {
        $R = dechex($r);
        if (strlen($R) < 2) {
            $R = '0'.$R;
        }

        $G = dechex($g);
        if (strlen($G) < 2) {
            $G = '0'.$G;
        }

        $B = dechex($b);
        if (strlen($B) < 2) {
            $B = '0'.$B;
        }

        return '#'.$R.$G.$B;
    }
}
