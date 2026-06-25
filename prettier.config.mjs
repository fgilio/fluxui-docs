/** @type {import("prettier").Config} */
export default {
  proseWrap: "never",
  overrides: [
    {
      files: ["*.md", "*.mdx"],
      options: {
        embeddedLanguageFormatting: "off",
      },
    },
    {
      files: ["*.yml", "*.yaml"],
      options: {
        proseWrap: "preserve",
      },
    },
  ],
};
