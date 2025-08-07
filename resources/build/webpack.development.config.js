const webpack = require("webpack");
const path = require("path");
const pathDistribution = "dist/";
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const dotenv = require("dotenv").config({ path: path.resolve(__dirname, "../../.env") });
const app_url = new URL(dotenv.parsed.APP_URL);

const is_https = app_url.protocol.includes("https");

const https = {
    cert: dotenv.parsed.APP_CERT,
    key: dotenv.parsed.APP_CERT_KEY
}

module.exports = {
    devServer: {
        client: {
            logging: 'none'
        },
        server: !is_https ? "http" : {
            type: "https",
            options: https
        },
        client: {
            logging: 'log'
        },
        host: app_url.host,
        port: 8080,
        static: false,
        compress: true,
        devMiddleware: {
            writeToDisk: true,
        },
        hot: true,
    },
    entry: path.resolve(__dirname, "./entry.js"),
    output: {
        filename: "bundle.js",
        chunkFilename: "[name].[chunkhash:8].chunk.js",
        path: path.resolve(__dirname, "../../public/" + pathDistribution),
        publicPath: pathDistribution,
        clean: true,
    },
    mode: "development",
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    { loader: MiniCssExtractPlugin.loader, options: { publicPath: "" } },
                    { loader: "css-loader", options: { importLoaders: 1, sourceMap: false } },
                    { loader: "postcss-loader", options: { postcssOptions: {
                        plugins: [
                            require("autoprefixer")
                        ]
                    } } }
                ],
            },
            {
                test: /\.scss$/,
                use: [
                    { loader: MiniCssExtractPlugin.loader, options: { publicPath: "" } },
                    { loader: "css-loader", options: { importLoaders: 1, sourceMap: false } },
                    { loader: "postcss-loader", options: { postcssOptions: {
                        plugins: [
                            require("autoprefixer")
                        ]
                    } } },
                    { loader: "sass-loader", options: {
                        implementation: require("sass"),
                        sourceMap: false,
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
    plugins: [
        new BrowserSyncPlugin({
            notify: false,
            host: app_url.host,
            open: false,
            proxy: dotenv.parsed.APP_URL,
            https: is_https ? https : false
        }, {
            reload: false
        }),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: true,
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false
        }),
        new MiniCssExtractPlugin({
            filename: "style.css",
        })
    ],
    resolve: {
        alias: {
            "vue": "vue/dist/vue.esm-bundler.js"
        }
    },
    externals: {
        Stripe: "Stripe",
        paypal: "paypal",
    },
};