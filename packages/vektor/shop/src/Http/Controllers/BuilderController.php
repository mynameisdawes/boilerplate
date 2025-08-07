<?php

namespace Vektor\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Image;
use setasign\Fpdi\Tcpdf\Fpdi;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\OneCRM\Models\Quote;
use Vektor\OneCRM\Models\SalesOrder;
use Vektor\Shop\Formatter;
use Vektor\Shop\Models\Product;

class BuilderController extends ApiController
{
    // Offsets and reference points for the PDF template.
    // If the template changes size these will need updating as everything has to be placed manually
    public const LEFT = 0;
    public const TOP = 90;
    public const RIGHT = 0;
    public const BOTTOM = 90;
    public const WIDTH = 1190;
    public const HEIGHT = 841;
    public const H_PADDING = 10;
    public const V_PADDING = 20;
    private $size;
    private $width;
    private $h_space;
    private $v_space;
    private $x_start;
    private $y_centre;
    private $model;
    private $blanks;
    private $image_only;

    // Fetch the original upload
    public function original(Request $request, $model_id, $design, $area)
    {
        try {
            return $this->fetch_upload($model_id, $design, $area, 'original', $request->input('model'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    // Fetch the resized upload
    public function resized(Request $request, $model_id, $design, $area)
    {
        try {
            return $this->fetch_upload($model_id, $design, $area, 'resized', $request->input('model'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     *  Generate a preview - either PDF or tiled images.
     *
     * @param $request  - laravel request
     * @param $model_id - Id of the OneCRM model
     * @param $design   - Identifier of the design within the Order or Quote (eg design01), if blank all designs will display
     * @param $variant  - Which variant of the design to preview (eg white), if blank all variants will display
     * @param $area     - which area of the product to see (eg front), if blank all areas will display
     *
     * @return Either a PDF or Http response Image
     */
    public function preview(Request $request, $model_id, $design = null, $variant = null, $area = null)
    {
        $this->setModel($model_id, $request->input('model'));

        // Return the full pdf or only an image
        $this->image_only = filter_var($request->input('image'), FILTER_VALIDATE_BOOLEAN);

        // Size of each image in px
        $this->size = filter_var($request->input('size'), FILTER_VALIDATE_INT) ? $request->input('size') : 1200;

        // Show areas without prints or not
        $this->blanks = null !== $request->input('blanks') ? filter_var($request->input('blanks'), FILTER_VALIDATE_BOOLEAN) : null;

        if ($this->model && isset($this->model['customisations_data'])) {
            // Object that is returned
            $preview = $this->image_only ? null : $this->initialize_pdf();
            $filename = 'preview_';
            $filename .= $this->model['number'];
            $customisations_data = collect(json_decode($this->model['customisations_data'], true));

            try {
                if (null == $design) {
                    // Preview whole order
                    $result = $this->preview_order($preview, $customisations_data);

                    if ($this->image_only) {
                        return $result->response();
                    }
                    $preview = $result;
                } else {
                    $filename .= "_{$design}";
                    $customisation = $this->get_customisation($customisations_data, strtolower($design));
                    if (null == $variant) {
                        // Preview one design in all colours
                        $result = $this->preview_design($preview, $customisation);
                        if ($this->image_only) {
                            return $result->response();
                        }
                        $preview = $result;
                    } else {
                        $filename .= "_{$variant}";
                        if (null == $area) {
                            // Preview all areas of one colour
                            $result = $this->preview_variant($preview, $customisation, strtolower($variant));
                            if ($this->image_only) {
                                return $result->response();
                            }
                            $preview = $result;
                        } else {
                            // Preview one area of one colour
                            $filename .= "_{$area}";
                            $result = $this->preview_variant($preview, $customisation, strtolower($variant), strtolower($area));
                            if ($this->image_only) {
                                return $result->response();
                            }
                            $preview = $result;
                        }
                    }
                }
            } catch (\Exception $e) {
                abort(404);
            }
            $preview->lastPage();

            // Return the file and download it
            // return $preview->Output($filename . '.pdf', 'D');
            // Return the pdf as an HTTP response
            return $preview->Output($filename.'.pdf', 'I');
            // return response($preview->Output($filename . '.pdf', 'S'))
            //         ->header('Content-Type', 'application/pdf')
            //         ->header('Cache-Control', 'no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            //         ->header('Content-Disposition', 'inline; filename="' . $filename .'.pdf"');
        }
        abort(404);
    }

    // Fetch an upload
    private function fetch_upload($model_id, $design, $area, $upload, $model_name = 'sales_order')
    {
        $this->setModel($model_id, $model_name);
        if ($this->model) {
            if ($this->model['customisations_data']) {
                $customisations_data = collect(json_decode($this->model['customisations_data'], true));
                $customisation = $this->get_customisation($customisations_data, $design);
                $design = current(array_filter($customisation['designs'], function ($side) use ($area) {
                    return strtolower($side['label']) == $area;
                }));
                $file = public_path($design['file'][$upload]);
                $file_pathinfo = pathinfo($file);
                $file_mime_type = mime_content_type($file);

                return response(file_get_contents($file))
                    ->header('Content-Type', $file_mime_type)
                    ->header('Content-Disposition', 'inline; filename="'.$file_pathinfo['basename'].'"')
                ;
            }
        } else {
            abort(404);
        }
    }

    /** Try to set a CRM model based on the query param.
     *  Or, check SOs the Quotes if one is not set.
     *
     * @param mixed $model_id
     * @param mixed $model_name
     */
    private function setModel($model_id, $model_name = 'sales_order')
    {
        switch ($model_name) {
            case 'quote':
                $model = new Quote();
                $model_response = $model->show($model_id, ['billing_account', 'customisations_data', 'due_date', 'quote_number']);
                if ($model_response) {
                    $model_response['number'] = $model_response['quote_number'];
                } else {
                    abort(404);
                }

                break;

            case 'sales_order':
                $model = new SalesOrder();
                $model_response = $model->show($model_id, ['billing_account', 'customisations_data', 'due_date', 'so_number']);
                if ($model_response) {
                    $model_response['number'] = $model_response['so_number'];
                } else {
                    abort(404);
                }

                break;

            default:
                $model = new SalesOrder();
                $model_response = $model->show($model_id, ['billing_account', 'customisations_data', 'due_date', 'so_number']);
                if ($model_response) {
                    $model_response['number'] = $model_response['so_number'];
                } else {
                    $model = new Quote();
                    $model_response = $model->show($model_id, ['billing_account', 'customisations_data', 'due_date', 'quote_number']);
                    if ($model_response) {
                        $model_response['number'] = $model_response['quote_number'];
                    } else {
                        abort(404);
                    }
                }

                break;
        }
        $this->model = $model_response;
    }

    private function preview_order($preview, $customisations)
    {
        $order_products = $this->fetch_order_products($customisations);
        $parents = $this->fetch_parents($order_products, 2);
        foreach ($customisations as $id => $customisation) {
            $designs = $customisation['designs'];
            $customisation_products = $order_products[$id];
            $preview = $this->preview_design($preview, $customisation, $customisation_products, $parents);
        }

        return $preview;
    }

    private function preview_design($preview, $customisation, $products = null, $parents = null)
    {
        $designs = $customisation['designs'];
        $products = $products ?? $this->fetch_design_products($customisation)->groupBy('parent_id');
        if ($products->isEmpty()) {
            throw new \Exception();
        }
        $parents = $parents ?? $this->fetch_parents($products);

        foreach ($products as $parent_id => $children) {
            $parent = $parents[$parent_id];
            $variants = $children->groupBy('attributes.0.value');
            $print_images = $this->print_images($designs, $parent['configuration']['builder_config']);
            foreach ($variants as $variant => $products) {
                $sizes = [];
                foreach ($products as $product) {
                    foreach ($product['attributes'] as $attribute) {
                        if ('size' == $attribute['name']) {
                            $sizes[] = $attribute['value_label'];
                        }
                    }
                }
                $product = $products[0];
                $colour = 'N/A';
                foreach ($product['attributes'] as $attribute) {
                    if ('colour' == $attribute['name']) {
                        $colour = $attribute['value_label'];
                    }
                }
                $page_data = [
                    'garment' => $parent['name'],
                    'colour' => $colour,
                    'sizes' => count($sizes) > 0 ? implode(', ', $sizes) : 'N/A',
                ];

                $preview = $this->add_page($preview, $page_data, $parent['configuration']['builder_config'], $product['configuration']['builder_images'], $print_images, $this->blanks ?? true);
            }
        }

        return $preview;
    }

    private function preview_variant($preview, $customisation, $variant, $area = null)
    {
        $designs = $customisation['designs'];

        $products = $this->fetch_design_products($customisation)->filter(function ($product) use ($variant) {
            return $product['attributes'][0]['value'] == $variant;
        })->groupBy('parent_id');

        if ($products->isEmpty()) {
            throw new \Exception();
        }

        foreach ($products as $parent_id => $children) {
            $product = $children[0];
            $parent = Formatter::product(Product::find($parent_id)->toArray());

            if (isset($area)) {
                $parent['configuration']['builder_config'] = array_filter($parent['configuration']['builder_config'], function ($print_data) use ($area) {
                    return strtolower($print_data['label']) == $area;
                });
            }

            $print_images = $this->print_images($designs, $parent['configuration']['builder_config']);

            $colour = 'N/A';
            $sizes = [];
            foreach ($product['attributes'] as $attribute) {
                if ('colour' == $attribute['name']) {
                    $colour = $attribute['value_label'];
                } elseif ('size' == $attribute['name']) {
                    $sizes[] = $attribute['value_label'];
                }
            }
            $page_data = [
                'garment' => $parent['name'],
                'colour' => $colour,
                'sizes' => count($sizes) > 0 ? implode(', ', $sizes) : 'N/A',
            ];

            $preview = $this->add_page($preview, $page_data, $parent['configuration']['builder_config'], $product['configuration']['builder_images'], $print_images, $this->blanks ?? !isset($area), $area);
        }

        return $preview;
    }

    // Returns the images to be printed onto garments (user uploads)
    private function print_images($designs, $print_config)
    {
        $print_images = [];
        foreach ($print_config as $print_side => $print_data) {
            if (isset($designs[$print_side])) {
                $position_label = $designs[$print_side]['position'];
                $position = $print_data['positions'][$position_label];
                $print_data = $this->print_positioning($position['boundaries'], $designs[$print_side]);
                $print_file = url($designs[$print_side]['file']['resized']);
                $print_images[$print_side] = $this->print_image($print_file, $print_data);
            }
        }

        return $print_images;
    }

    // Maths for size and position of prints
    private function print_positioning($base_bounds, $design)
    {
        // Upload area boundaries in px
        $bounds = [
            'w' => $this->size * $base_bounds['w'],
            'x' => $this->size * $base_bounds['x'],
            'y' => $this->size * $base_bounds['y'],
            'h' => ($base_bounds['w'] * $this->size) * ($base_bounds['mm']['h'] / $base_bounds['mm']['w']),
        ];

        return [
            'w' => $bounds['w'] * ($design['dimensions']['w'] / $base_bounds['mm']['w']),
            'h' => $bounds['h'] * ($design['dimensions']['h'] / $base_bounds['mm']['h']),
            'x' => ($design['dimensions']['x'] * $bounds['w'] / $base_bounds['mm']['w']) + $bounds['x'],
            'y' => ($design['dimensions']['y'] * $bounds['h'] / $base_bounds['mm']['h']) + $bounds['y'],
            'rotation' => $design['rotation'],
        ];
    }

    private function print_image($print_file, $print_data)
    {
        // Put the print image on a blank canvas the size of the final garment image
        $canvas = \Image::canvas($this->size, $this->size);
        $print_img = \Image::make($print_file)
        // ->rotate($print_data['rotation'])
            ->resize($print_data['w'], $print_data['h'])
        ;
        $canvas->insert($print_img, 'top-left', intval(round($print_data['x'])), intval(round($print_data['y'])));

        return $canvas;
    }

    private function preview_image($base_img, $print_img = null)
    {
        // Combine the base garment image and the print image
        $image = \Image::make($base_img)
            ->resize($this->size, $this->size)
        ;
        if (isset($print_img)) {
            $image->insert($print_img);
        }

        return $image;
    }

    // Add a page to the pdf/add row to the image
    private function add_page($preview, $page_data, $print_config, $product_images, $print_images = [], $show_blank = true, $area = null)
    {
        if (!$show_blank && empty($print_images)) {
            abort(404);
        }

        if (false == $this->image_only) {
            // Fill in the text for the pdf template
            $so_data = [
                'customer' => $this->model['billing_account'],
                'order_number' => $this->model['number'],
                'order_date' => $this->model['due_date'] ?? 'N/A',
                'process' => 'DTF',
            ];
            $page_data = array_merge($page_data, $so_data);
            // $page_data = array_map(function($data) {
            //     return strtoupper($data);
            // }, $page_data);
            $pageCount = $preview->setSourceFile(public_path('builder/proof--template.pdf'));
            $tplIdx = $preview->importPage(1);
            $preview->AddPage();
            $preview->useTemplate($tplIdx);

            $text = <<<EOD
            <table>
                <tbody>
                    <tr>
                        <td>{$page_data['customer']}</td>
                    </tr>
                    <tr>
                        <td>{$page_data['order_date']}</td>
                    </tr>
                    <tr>
                        <td>{$page_data['order_number']}</td>
                    </tr>
                </tbody>
            </table>
            EOD;

            $preview->setFontSize(10);
            $preview->setXY(95, 31.5);
            $preview->setCellHeightRatio(1.5);
            $preview->writeHTML($text, false, false, true, false, 'L');

            $text = <<<EOD
            <table>
                <tbody>
                    <tr>
                        <td>{$page_data['sizes']}</td>
                    </tr>
                    <tr>
                        <td>{$page_data['garment']}</td>
                    </tr>
                    <tr>
                        <td>{$page_data['colour']}</td>
                    </tr>
                </tbody>
            </table>
            EOD;
            $preview->setXY(415, 31.5);
            $preview->writeHTML($text, false, false, true, false, 'L');
        }

        // Number of images in the page/rows
        $count = !isset($area) && $show_blank ? count($print_config) : (!empty($print_images) ? count($print_images) : 1);
        if (false != $this->image_only) {
            $rows = ceil($count / 3);
            $canvas = \Image::canvas($this->size * min(3, $count), $rows * $this->size, '#ffffff');
        }
        $added = 0;
        for ($i = 0; $i < count($print_config); ++$i) {
            $print_side = array_keys($print_config)[$i];
            if (!isset($area) || strtolower($print_config[$print_side]['label']) == $area) {
                if ($show_blank || isset($print_images[$print_side])) {
                    $base_img = $product_images[$print_side.'_image'];
                    $image_obj = $this->preview_image($base_img, $print_images[$print_side] ?? null);
                    if (false == $this->image_only) {
                        // How large each image should be on each page (pdf only)
                        $size = min($this->h_space / $count, $this->v_space);
                        // Compensate for pdf header
                        $y_offset = $this->y_centre - ($size / 2);
                        // The remaining white space on the page (used to centre the images on the page)
                        $remaining = $this->h_space - ($count * $size);
                        // Space between images
                        $x_offset = $this->x_start + ($remaining / 2);
                        $image = (string) $image_obj->encode('png');
                        // Resizing and changing dpi big increase to speed - slowest part of whole process
                        $preview->Image('@'.$image, $x_offset + ($size * $added), $y_offset, $size, $size, 'PNG', '', '', true, 72);
                        // 300dpi by default
                        // $preview->Image('@' . $image, $x_offset + ($size * $added), $y_offset, $size, $size, 'PNG');
                        ++$added;
                    } else {
                        $canvas->insert($image_obj, 'top-left', $this->size * ($i % 3), $this->size * floor($i / 3));
                    }
                }
            }
            $page_data[strtolower($print_config[$print_side]['label'])] = isset($print_images[$print_side]) ? 'Yes' : 'No';
        }

        if (false != $this->image_only) {
            // If not returning a pdf return the updated final image now
            if (null == $preview) {
                $preview = $canvas;
            } else {
                $height = $preview->height() + $canvas->height();
                $tmp = \Image::canvas($canvas->width(), $height);
                $tmp->insert($preview, 'top-left');
                $tmp->insert($canvas, 'bottom-left');
                $preview = $tmp;
            }

            return $preview;
        }

        // more pdf text stuff
        $text = <<<EOD
        <table>
            <tbody>
                <tr>
                    <td>{$page_data['process']}</td>
                </tr>
                <tr>
                    <td>{$page_data['front']}</td>
                </tr>
                <tr>
                    <td>{$page_data['back']}</td>
                </tr>
            </tbody>
        </table>
        EOD;
        $preview->setXY(735, 31.5);
        $preview->writeHTML($text, false, false, true, false, 'L');

        return $preview;
    }

    // Old functions for image previews
    // public function design_previews($customisation, $design)
    // {
    //     $products = $this->fetch_design_products($customisation);
    //     $variants = $products->groupBy('attributes.0.value')->keys();
    //     $offset = $this->size;
    //     $rows = ceil($variants->count() / 3);
    //     $image = Image::canvas($this->size * min(3, $variants->count()), $rows * $this->size, "#ffffff");
    //     for ($i=0; $i < $variants->count(); $i++) {
    //         $image->insert($this->variant_preview($customisation, $design, $area, $variants[0]), 'top-left', $offset * ($i % 3), $this->size * (floor($i/3)));
    //     }
    //     return $image->response('png');
    // }

    // public function area_previews($customisation, $design, $area)
    // {
    //     $products = $this->fetch_design_products($customisation);
    //     $variants = $products->groupBy('attributes.0.value')->keys();
    //     $offset = $this->size;
    //     $rows = ceil($variants->count() / 3);
    //     $image = Image::canvas($this->size * min(3, $variants->count()), $rows * $this->size, "#ffffff");
    //     for ($i=0; $i < $variants->count(); $i++) {
    //         $image->insert($this->variant_preview($customisation, $design, $area, $variants[0]), 'top-left', $offset * ($i % 3), $this->size * (floor($i/3)));
    //     }
    //     return $image->response('png');
    // }

    private function fetch_order_products($customisations)
    {
        return $customisations->map(function ($customisation) {
            return $this->fetch_design_products($customisation)->keyBy('id')->groupBy('parent_id');
        });
    }

    private function fetch_design_products($customisation)
    {
        $products = Product::whereIn('sku', $customisation['skus'])->get()->toArray();

        return collect(Formatter::products($products));
    }

    // Fetch the parent products to be used to avoid repeated searches for the same products
    private function fetch_parents($products, $i = 1)
    {
        $parents = Product::whereIn('id', $products->flatten($i)->groupBy('parent_id')->keys())->get();

        return collect(Formatter::products($parents->toArray()))->keyBy('id');
    }

    private function fetch_product($products, $variant)
    {
        return $products->filter(function ($product) use ($variant) {
            foreach ($product['attributes'] as $attribute) {
                if ($attribute['value'] == $variant) {
                    return true;
                }
            }

            return false;
        })->first();
    }

    // Base values and options for the pdf
    private function initialize_pdf()
    {
        // Initialize the values for the pdf
        // Constants set at top of class

        // Space available to put images in
        $this->h_space = self::WIDTH - self::LEFT - self::RIGHT - self::H_PADDING;
        $this->v_space = self::HEIGHT - self::TOP - self::BOTTOM - self::V_PADDING;

        // Half the available height - half the size of the image
        $this->y_centre = self::TOP + ($this->v_space / 2) + (self::V_PADDING / 2);
        $this->x_start = self::LEFT + (self::H_PADDING / 2);

        // Fdpi is based on TCPDF - I think used for the template stuff.
        // Use TCPDF Docs - best site wbcomdesigns
        // $pdf = new \TCPDF("landscape", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf = new Fpdi('L', 'pt', [self::WIDTH, self::HEIGHT]);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->setFontSubsetting(false); // supposed to improve performance

        // Set margins
        $pdf->SetMargins(self::LEFT, self::TOP, self::RIGHT);

        return $pdf;
    }

    // Convert design url param to the array index
    // bit of a janky comporomise for readability of urls
    private function get_customisation($data, $design)
    {
        $design = intval(substr($design, strlen($design) - 2)) - 1;
        $customisation_id = $data->keys()->get($design);

        return $data->get($customisation_id);
    }
}
