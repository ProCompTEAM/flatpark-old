using System;

namespace DataCenter.Common.Network.HttpWeb.Attributes
{
    public class WebRoute : Attribute
    {
        public readonly string Value;

        public WebRoute(string value)
        {
            Value = value;
        }
    }
}
