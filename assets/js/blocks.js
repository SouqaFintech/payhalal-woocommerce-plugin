const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;
const { decodeEntities } = window.wp.htmlEntities;
const { createElement } = window.wp.element;

const settings = getSetting("payhalal_data", {});

const label = decodeEntities(settings.title || "PayHalal");

const Content = () => {
  return createElement("div", {
    dangerouslySetInnerHTML: {
      __html: settings.description || "Pay securely via PayHalal.",
    },
  });
};

registerPaymentMethod({
  name: "payhalal",
  label,
  content: createElement(Content, null),
  edit: createElement(Content, null),
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports?.features || ["products"],
  },
});
