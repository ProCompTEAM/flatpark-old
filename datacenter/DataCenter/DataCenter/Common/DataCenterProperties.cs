using DataCenter.Infrastructure.Generic;
using System.Collections.Generic;

namespace DataCenter.Common
{
    public class DataCenterProperties : Properties
    {
        public DataCenterProperties() : base(Defaults.PropertiesDataCenterFilename)
        {
            SetDefaultsForConfig();
        }

        public string WebListenerAddress => GetValue("web-listener-addr");

        public int WebListenerPort => int.Parse(GetValue("web-listener-port"));

        private void SetDefaultsForConfig()
        {
            Dictionary<string, string> defaults = new Dictionary<string, string>()
            {
                { "web-listener-addr", "127.0.0.1" },
                { "web-listener-port", "19000" }
            };
            SetDefaults(defaults, true);
        }
    }
}
