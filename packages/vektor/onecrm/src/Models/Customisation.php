<?php

namespace Vektor\OneCRM\Models;

class Customisation
{
    protected $designs = [];
    protected $skus = [];
    protected $note;
    protected $directory_name;
    protected $design_dir;
    protected $rel_directory;
    protected $abs_directory;
    private $id;

    private $image_suffix = [
        'original_upload' => '--upload--original',
        'resized_upload' => '--upload--resized',
        'preview' => '--preview',
    ];

    private $img_url_prefix = '';

    private $original = '';
    private $resized = '';

    private $comment = '';

    private $artwork_status = 0;

    public function __construct($item, $options)
    {
        $this->id = data_get($item, 'id');
        $this->designs = data_get($item, 'designs');
        $this->note = data_get($item, 'note');
        $this->create_comment($options);

        return $this;
    }

    public function get_id()
    {
        return $this->id;
    }

    public function get_resized()
    {
        return $this->resized;
    }

    public function get_original()
    {
        return $this->original;
    }

    public function get_comment()
    {
        return $this->comment;
    }

    public function get_designs()
    {
        return $this->designs;
    }

    public function get_skus()
    {
        return $this->skus;
    }

    public function get_note()
    {
        return $this->note;
    }

    public function add_sku($sku)
    {
        if (in_array($sku, $this->skus)) {
            return false;
        }
        $this->skus[] = $sku;

        return true;
    }

    public function create_comment($options)
    {
        if ($options->get('so_id')) {
            $design_dir = null;
            foreach ($this->designs as $design => &$data) {
                if (isset($data['links']['original'])) {
                    $img_url_prefix_match = preg_match('/.+(design[\d]{1,3})\//', $data['links']['original'], $img_url_prefix_matches);
                    if ($img_url_prefix_match) {
                        $design_dir = $img_url_prefix_matches[1];
                    }
                }

                break;
            }

            if ($design_dir) {
                $this->img_url_prefix = config('app.url').'/preview/'.$options->get('so_id')."/{$design_dir}/";
            } else {
                $this->img_url_prefix = config('app.url').'/preview/{{ SO_ID }}{{ DESIGN_DIR }}/';
            }
        } else {
            $this->img_url_prefix = config('app.url').'/preview/{{ SO_ID }}{{ DESIGN_DIR }}/';
        }

        $this->comment = "\n{$this->equalss_lg()} ARTWORK {$this->equalss_lg()}\n\n";
        $this->comment .= 'Printed and designed as proofed in app.';

        $this->comment .= "\n\n{$this->equalss_lg()} PREVIEW {$this->equalss_lg()}\n\n";
        $this->comment .= "{$this->img_url_prefix}\n";
        $this->preview = $this->img_url_prefix;

        foreach ($this->designs as $design => &$data) {
            $this->comment .= $this->design_comment($design, $data, $options);
            $this->artwork_status = max($data['uploadFeedback']['level'], $this->artwork_status);
        }

        $this->comment .= "\n\n{$this->equalss_lg()} NOTE {$this->equalss_lg()}\n\n";

        $this->comment .= !empty($this->note) ? "{$this->note}" : 'No note';
    }

    public function update_comment($so_id)
    {
        $this->comment = str_replace(['{{ SO_ID }}', '{{ DESIGN_DIR }}'], [$so_id, $this->design_dir], $this->comment);
        foreach ($this->designs as $design => &$data) {
            $data['links']['original'] = str_replace(['{{ SO_ID }}', '{{ DESIGN_DIR }}'], [$so_id, $this->design_dir], $data['links']['original']);
            $data['links']['resized'] = str_replace(['{{ SO_ID }}', '{{ DESIGN_DIR }}'], [$so_id, $this->design_dir], $data['links']['resized']);
        }
    }

    public function make_design_dir($id, $order_dir)
    {
        $i = 0;
        do {
            $inc = sprintf('%02d', ++$i);
            $dir = '/design'.$inc;
            $relative_path = $order_dir.$dir;
            $abs_path = public_path().$relative_path;
        } while (file_exists($abs_path));
        if (!file_exists($abs_path)) {
            mkdir($abs_path);
        }
        $this->abs_directory = $abs_path;
        $this->rel_directory = $relative_path;
        $this->design_dir = $dir;
    }

    public function handle_images()
    {
        foreach ($this->designs as &$design) {
            $this->design_image($design);
        }
    }

    public function get_artwork_status()
    {
        return $this->artwork_status;
    }

    private function design_comment($design, &$data, $options)
    {
        $data = $this->designs[$design];

        $comment_body = "\n\n{$this->equalss_sm()} ".strtoupper($data['label'])." {$this->equalss_sm()}\n\n";
        $area = strtolower($data['label']);
        if (!empty($data['file']['file_path'])) {
            $comment_body .= "Position:\n";
            $comment_body .= strtoupper($data['position'])."\n";
            $comment_body .= "Original Upload:\n";
            $comment_body .= "{$this->img_url_prefix}{$area}/original\n";
            $comment_body .= "Resized Upload:\n";
            $comment_body .= "{$this->img_url_prefix}{$area}/resized\n";

            $data['links'] = [];
            $data['links']['original'] = "{$this->img_url_prefix}{$area}/original";
            $data['links']['resized'] = "{$this->img_url_prefix}{$area}/resized";

            $comment_body .= "\nUpload Feedback:\n";
            $comment_body .= "Level {$data['uploadFeedback']['level']}: ";
            $comment_body .= "{$data['uploadFeedback']['message']}\n";

            $comment_body .= "\nPrint Dimensions approx. (W x H):\n";
            $px = $data['printPixelValues'];
            $mm = $data['dimensions'];
            $comment_body .= 'px: '.round($px['w']).' x '.round($px['h']);
            $comment_body .= "\nmm: ".round($mm['w']).' x '.round($mm['h']);
        } elseif (isset($data['links']['original'])) {
            $comment_body .= "Position:\n";
            $comment_body .= strtoupper($data['position'])."\n";
            $comment_body .= "Original Upload:\n";
            $comment_body .= "{$this->img_url_prefix}{$area}/original\n";
            $comment_body .= "Resized Upload:\n";
            $comment_body .= "{$this->img_url_prefix}{$area}/resized\n";

            $comment_body .= "\nUpload Feedback:\n";
            $comment_body .= "Level {$data['uploadFeedback']['level']}: ";
            $comment_body .= "{$data['uploadFeedback']['message']}\n";

            $comment_body .= "\nPrint Dimensions approx. (W x H):\n";
            $px = $data['printPixelValues'];
            $mm = $data['dimensions'];
            $comment_body .= 'px: '.round($px['w']).' x '.round($px['h']);
            $comment_body .= "\nmm: ".round($mm['w']).' x '.round($mm['h']);
        } else {
            $comment_body .= 'NONE';
        }

        return $comment_body;
    }

    private function design_image(&$data)
    {
        if (!empty($data['file']['file_path'])) {
            $design = strtolower($data['label']);
            if ('svg' == $data['file']['file_extension']) {
                $data['file'] = $this->convert__and_move_SVG($design, $data);
            } else {
                $data['file'] = [
                    'resized' => $this->resize_and_save_upload($design, $data),
                    'original' => $this->save_original_upload_to_folder($design, $data),
                ];
            }
        }
    }

    private function resize_and_save_upload($design, $data)
    {
        $path = "{$design}{$this->image_suffix['resized_upload']}.png";
        \Image::make($data['file']['file_path'])
            ->rotate(360 - $data['rotation']) // WHY DOES IT ROTATE ANTI CLOCKWISE!!!????? BRUH
            ->resize($data['printPixelValues']['w'], null, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->save("{$this->abs_directory}/{$path}")
        ;

        return "{$this->rel_directory}/{$path}";
    }

    private function convert__and_move_SVG($design, $data)
    {
        $input = $this->remove_base_url($data['file']['file_path']);
        $output = "{$design}{$this->image_suffix['resized_upload']}.png";
        $dpi = 300;
        $cmd = (env('BIN_PATH') ?? '/usr/bin/')."convert -verbose -units PixelsPerInch -background transparent -density {$dpi} -rotate {$data['rotation']} -resize {$data['printPixelValues']['w']} {$input} {$this->abs_directory}/{$output}";
        $result = shell_exec($cmd);
        if (null == $result || false == $result) {
            throw new \Exception("Could not convert SVG {$input}", 500);
        }
        $path = "{$design}{$this->image_suffix['original_upload']}.svg";
        rename($input, "{$this->abs_directory}/{$path}");

        return [
            'original' => "{$this->rel_directory}/{$path}",
            'resized' => "{$this->rel_directory}/{$output}",
        ];
    }

    private function move_preview($design, $data)
    {
        if (preg_match('/^previews/', $data['preview']) && file_exists(public_path().'/'.$data['preview'])) {
            rename($data['preview'], "{$this->abs_directory}/{$design}{$this->image_suffix['preview']}.png");
        }
    }

    private function save_original_upload_to_folder($design, $data)
    {
        $path = "{$design}{$this->image_suffix['original_upload']}.png";
        \Image::make($this->remove_base_url($data['file']['file_path']))
            ->save("{$this->abs_directory}/{$path}")
        ;

        return "{$this->rel_directory}/{$path}";
    }

    private function remove_base_url($url)
    {
        return public_path().'/'.ltrim(parse_url($url)['path'], '/');
    }

    private function equalss_sm()
    {
        return '====';
    }

    private function equalss_lg()
    {
        $sm = $this->equalss_sm();

        return str_repeat($sm, 2);
    }
}
