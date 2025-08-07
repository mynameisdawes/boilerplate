const webpack = require('webpack');
const path = require("path");
const glob = require('glob-all');
const pathDistribution = "dist/";
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require("terser-webpack-plugin");
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin');

module.exports = {
    entry: path.resolve(__dirname, "./entry.js"),
    output: {
        filename: "bundle.js",
        chunkFilename: '[name].[chunkhash:8].chunk.js',
        path: path.resolve(__dirname, "../../public/" + pathDistribution),
        publicPath: pathDistribution,
        clean: true,
    },
    mode: "production",
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    { loader: MiniCssExtractPlugin.loader, options: { publicPath: "" } },
                    { loader: "css-loader", options: { importLoaders: 1 } },
                    { loader: "postcss-loader", options: { postcssOptions: {
                        plugins: [
                            require('autoprefixer')
                        ]
                    } } }
                ],
            },
            {
                test: /\.scss$/,
                use: [
                    { loader: MiniCssExtractPlugin.loader, options: { publicPath: "" } },
                    { loader: "css-loader", options: { importLoaders: 1 } },
                    { loader: "postcss-loader", options: { postcssOptions: {
                        plugins: [
                            require('autoprefixer')
                        ]
                    } } },
                    { loader: "sass-loader", options: {
                        implementation: require("sass"),
                        api: "modern-compiler",
                        sassOptions: {
                            quietDeps: true
                        }
                    } }
                ],
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: "babel-loader",
                        options: {
                            presets: ["@babel/env"],
                            plugins: [
                                [
                                    "babel-plugin-root-import",
                                    {
                                        rootPathPrefix: "@/"
                                    }
                                ]
                            ]
                        },
                    },
                ],
            },
        ],
    },
    optimization: {
        minimize: true,
        minimizer: [
            new CssMinimizerPlugin(),
            new TerserPlugin()
        ],
    },
    plugins: [
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: false,
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false
        }),
        new MiniCssExtractPlugin({
            filename: "style.css",
        }),
        new PurgeCSSPlugin({
            paths: glob.sync([
                path.resolve(__dirname, '../../resources/views') + '/**/*',
                path.resolve(__dirname, './components') + '/**/*',
                path.resolve(__dirname, './mixins') + '/**/*',
                path.resolve(__dirname, './utilities') + '/**/*',
                path.resolve(__dirname, '../../packages/**/**/resources/views') + '/**/*',
            ], { nodir: true }),
            safelist: {
                standard: [/:where/, /:is/, /:has/, /swiper$/, /swiper/, /noUi$/, /noUi/, /carousel$/, /carousel/, /slide$/, /slide/, /vuejs3-datepicker$/, /vuejs3-datepicker/, /vuejs3-datepicker__calendar$/, /vuejs3-datepicker__calendar/, /day/, /day$/, /.carousel__pagination/, /.carousel__pagination$/, /.carousel-track/, /.carousel-track$/]
            },
            defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || []
        }),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js'
        }
    },
    externals: {
        Stripe: "Stripe",
        paypal: "paypal",
    },
};